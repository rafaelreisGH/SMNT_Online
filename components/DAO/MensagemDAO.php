<?php
// components/DAO/MensagemDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class MensagemDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Busca mensagens com filtros avançados
     */
    public function buscar(int $userId, array $f = [])
    {
        $pasta = $f['pasta'] ?? 'entrada';
        $sql = "SELECT m.*, u_rem.nome as remetente_nome, u_dest.nome as destinatario_nome, v.prefixo, v.numero 
            FROM mensagens m
            JOIN usuarios u_rem ON m.remetente_id = u_rem.id
            JOIN usuarios u_dest ON m.destinatario_id = u_dest.id
            LEFT JOIN viaturas v ON m.viatura_id = v.id
            WHERE 1=1";
        $params = [':u_id' => $userId];

        if ($pasta === 'entrada') {
            $sql .= " AND m.destinatario_id = :u_id AND m.excluida_destinatario = 0";
        } elseif ($pasta === 'enviadas') {
            $sql .= " AND m.remetente_id = :u_id AND m.excluida_remetente = 0";
        } elseif ($pasta === 'favoritas') {
            $sql .= " AND m.favorita = 1 AND ((m.destinatario_id = :u_id AND m.excluida_destinatario = 0) OR (m.remetente_id = :u_id AND m.excluida_remetente = 0))";
        }

        if (!empty($f['termo'])) {
            $sql .= " AND (m.assunto LIKE :termo OR m.conteudo LIKE :termo)";
            $params[':termo'] = '%' . $f['termo'] . '%';
        }

        if (!empty($f['lida']) || (isset($f['lida']) && $f['lida'] === "0")) {
            $sql .= " AND m.lida = :lida";
            $params[':lida'] = (int)$f['lida'];
        }

        $sql .= " ORDER BY m.criada_em DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Envia uma mensagem. 
     * Se unidadeId for passado, envia para todos os usuários daquela unidade.
     */
    public function enviar(array $data, $unidadeId = null)
    {
        $destinatarios = [];
        if ($unidadeId) {
            // Enviar para todos da unidade menos eu
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE unidade_id = :unid AND id != :rem");
            $stmt->execute([':unid' => $unidadeId, ':rem' => $data['remetente_id']]);
            $destinatarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            // Enviar para um usuário específico
            $destinatarios[] = $data['destinatario_id'];
        }

        if (empty($destinatarios)) return false;

        $sql = "INSERT INTO mensagens (remetente_id, destinatario_id, viatura_id, assunto, conteudo) 
                VALUES (:rem, :dest, :vtr, :ass, :cont)";
        $stmt = $this->conn->prepare($sql);

        foreach ($destinatarios as $destId) {
            $stmt->execute([
                ':rem'  => $data['remetente_id'],
                ':dest' => $destId,
                ':vtr'  => !empty($data['viatura_id']) ? $data['viatura_id'] : null,
                ':ass'  => $data['assunto'],
                ':cont' => $data['conteudo']
            ]);
        }
        return true;
    }

    public function editar(int $id, int $userId, $novoConteudo)
    {
        // Verifica se ainda está no prazo de 10 minutos
        $sql = "UPDATE mensagens SET conteudo = :cont, editada = 1, editada_em = NOW() 
                WHERE id = :id AND remetente_id = :u_id 
                AND criada_em > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':cont' => $novoConteudo, ':id' => $id, ':u_id' => $userId]) && $stmt->rowCount() > 0;
    }

    public function marcarLida(int $id, int $userId)
    {
        $stmt = $this->conn->prepare("UPDATE mensagens SET lida = 1 WHERE id = :id AND destinatario_id = :u_id");
        return $stmt->execute([':id' => $id, ':u_id' => $userId]);
    }

    public function favoritar(int $id, int $userId, $status)
    {
        $stmt = $this->conn->prepare("UPDATE mensagens SET favorita = :status WHERE id = :id AND (destinatario_id = :u_id OR remetente_id = :u_id)");
        return $stmt->execute([':status' => $status, ':id' => $id, ':u_id' => $userId]);
    }

    /**
     * Realiza a exclusão lógica (oculta para quem excluiu)
     */
    /**
     * Realiza a exclusão lógica da mensagem (oculta para quem excluiu)
     */
    public function excluir(int $id, int $userId): bool
    {
        // Esta query tenta marcar como excluída tanto no lado do remetente quanto do destinatário.
        // O MySQL só aplicará a alteração onde a condição de ID do usuário bater.
        $sql = "UPDATE mensagens SET 
                excluida_remetente = CASE WHEN remetente_id = :u_id THEN 1 ELSE excluida_remetente END,
                excluida_destinatario = CASE WHEN destinatario_id = :u_id THEN 1 ELSE excluida_destinatario END
            WHERE id = :id AND (remetente_id = :u_id OR destinatario_id = :u_id)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':u_id', $userId, PDO::PARAM_INT);

            $res = $stmt->execute();

            // Retorna true apenas se alguma linha foi realmente afetada
            return $res && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao excluir mensagem: " . $e->getMessage());
            return false;
        }
    }
}
