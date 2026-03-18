<?php
// components/DAO/NotificacaoDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class NotificacaoDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Busca notificações não lidas do usuário logado
     */
    public function listarNaoLidasPorUsuario(int $usuarioId)
    {
        $sql = "SELECT * FROM notificacoes WHERE usuario_id = :u_id AND lida = 0 ORDER BY criada_em DESC LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':u_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marca uma notificação como lida
     */
    public function marcarComoLida(int $id, int $usuarioId)
    {
        $sql = "UPDATE notificacoes SET lida = 1 WHERE id = :id AND usuario_id = :u_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':u_id' => $usuarioId]);
    }

    /**
     * O "CÉREBRO" DO SISTEMA: Gera notificações para os gestores certos
     * @param int $unidadeEventoId A unidade onde o fato ocorreu
     * @param string $titulo
     * @param string $mensagem
     * @param string $tipo (avaria, manutencao, etc)
     * @param string $linkId URL para carregar via loadPage()
     */
    public function dispararEvento(int $unidadeEventoId, $titulo, $mensagem, $tipo, $linkId = null)
    {
        // Busca todos os usuários que devem saber deste evento:
        // 1. Todos os Administradores e Gestores Centrais
        // 2. Gestor Regional do Comando da unidade afetada
        // 3. Gestores Locais da própria unidade
        $sql = "SELECT DISTINCT u.id 
                FROM usuarios u
                JOIN usuario_perfis up ON u.id = up.usuario_id
                JOIN perfis p ON up.perfil_id = p.id
                JOIN unidades un ON u.unidade_id = un.id
                WHERE p.nome IN ('Administrador', 'Gestor Central') -- Regra 1
                OR (p.nome = 'Gestor Regional' AND un.comando_regional_id = (SELECT comando_regional_id FROM unidades WHERE id = :unid_id)) -- Regra 2
                OR (p.nome = 'Gestor Local' AND u.unidade_id = :unid_id) -- Regra 3";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':unid_id' => $unidadeEventoId]);
        $destinatarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($destinatarios) {
            $insert = "INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo, link_id) 
                       VALUES (:u_id, :titulo, :msg, :tipo, :link)";
            $stmtInsert = $this->conn->prepare($insert);

            foreach ($destinatarios as $userId) {
                $stmtInsert->execute([
                    ':u_id'   => $userId,
                    ':titulo' => $titulo,
                    ':msg'    => $mensagem,
                    ':tipo'   => $tipo,
                    ':link'   => $linkId
                ]);
            }
        }
    }

    /**
     * Busca o histórico de notificações (lidas e não lidas) do usuário
     */
    public function listarTodasPorUsuario(int $usuarioId)
    {
        $sql = "SELECT * FROM notificacoes 
            WHERE usuario_id = :u_id 
            ORDER BY criada_em DESC LIMIT 50";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':u_id' => $usuarioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Roda um check-up e cria notificações de sistema para itens que entraram em zona crítica
     * Pode ser chamado no login ou por um cronjob
     */
    public function verificarGerarAlertasAutomaticos(int $usuarioId, int $unidadeId, int $crbmId, string $perfil)
    {
        $dash = new DashboardDAO();
        $criticos = $dash->getAlertasPreventiva('critico');

        foreach ($criticos as $c) {
            $titulo = "URGENTE: Manutenção Vencida";
            $msg = "A viatura {$c['prefixo']} {$c['numero']} ultrapassou o limite para: {$c['item_nome']}.";

            // Verifica se já existe uma notificação IGUAL não lida para este usuário hoje
            // (Evita spam de notificações)
            $check = "SELECT id FROM notificacoes WHERE usuario_id = :u AND titulo = :t AND lida = 0 AND criada_em > DATE_SUB(NOW(), INTERVAL 1 DAY)";
            $stmt = $this->conn->prepare($check);
            $stmt->execute([':u' => $usuarioId, ':t' => $titulo]);

            if (!$stmt->fetch()) {
                $this->dispararEvento($c['lotacao_atual_id'], $titulo, $msg, 'vencimento', 'pages/dashboard.php');
            }
        }
    }
}
