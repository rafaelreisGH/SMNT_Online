<?php
// components/DAO/MovimentacaoDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';
require_once __DIR__ . '/NotificacaoDAO.php';

class MovimentacaoDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Inicia uma transferência (Apenas Central/Admin)
     */
    public function solicitarTransferencia(array $d)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Registra a movimentação
            $sql = "INSERT INTO movimentacoes_lotacao (viatura_id, unidade_origem_id, unidade_destino_id, data_movimentacao, documento_autorizacao, status) 
                    VALUES (:v_id, :origem, :destino, NOW(), :doc, 'Em Trânsito')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':v_id'    => $d['viatura_id'],
                ':origem'  => $d['unidade_origem_id'],
                ':destino' => $d['unidade_destino_id'],
                ':doc'     => $d['documento']
            ]);

            // 2. Atualiza a lotação na tabela viaturas IMEDIATAMENTE 
            // (Isso faz com que o destino passe a ver a viatura via RBAC)
            $sqlVtr = "UPDATE viaturas SET lotacao_atual_id = :destino WHERE id = :v_id";
            $stmtVtr = $this->conn->prepare($sqlVtr);
            $stmtVtr->execute([':destino' => $d['unidade_destino_id'], ':v_id' => $d['viatura_id']]);

            // 3. Notifica o Gestor Local de Destino
            $nDAO = new NotificacaoDAO();
            $nDAO->dispararEvento(
                (int)$d['unidade_destino_id'],
                "Viatura em Transferência",
                "Uma viatura está sendo enviada para sua unidade. Documento: " . $d['documento'],
                "sistema",
                "pages/confirmar_recebimento.php"
            );

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Confirma que a viatura chegou (Gestor Local/Regional)
     */
    public function confirmarRecebimento(int $movId, int $usuarioId)
    {
        $sql = "UPDATE movimentacoes_lotacao SET 
                status = 'Recebido', 
                data_recebimento = NOW(), 
                usuario_recebeu_id = :u_id 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':u_id' => $usuarioId, ':id' => $movId]);
    }

    /**
     * Lista todas as transferências com status 'Em Trânsito' destinadas ao escopo do usuário
     */
    public function listarTransferenciasPendentes(array $userAuth)
    {
        $sql = "SELECT m.*, v.prefixo, v.numero, v.placa,
                   u_orig.nome as unidade_origem, 
                   u_dest.nome as unidade_destino
            FROM movimentacoes_lotacao m
            JOIN viaturas v ON m.viatura_id = v.id
            JOIN unidades u_orig ON m.unidade_origem_id = u_orig.id
            JOIN unidades u_dest ON m.unidade_destino_id = u_dest.id
            WHERE m.status = 'Em Trânsito'";

        $params = [];

        // RBAC: Filtrar pelo destino
        if ($userAuth['perfil'] === 'Gestor Local') {
            $sql .= " AND m.unidade_destino_id = :unid";
            $params[':unid'] = $userAuth['unidade_id'];
        } elseif ($userAuth['perfil'] === 'Gestor Regional') {
            $sql .= " AND u_dest.comando_regional_id = :crbm";
            $params[':crbm'] = $userAuth['comando_regional_id'];
        }

        $sql .= " ORDER BY m.data_movimentacao DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
