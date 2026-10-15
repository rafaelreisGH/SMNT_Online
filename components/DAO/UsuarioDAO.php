<?php
// components/DAO/UsuarioDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class UsuarioDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    // Usado pelo ADMIN para criar novos usuários
    public function criarUsuario(array $data)
    {
        $sql = "INSERT INTO usuarios (nome, email, cpf, senha_hash, unidade_id, trocar_senha, ativo) 
                VALUES (:nome, :email, :cpf, :hash, :unidade_id, 1, 1)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':cpf', preg_replace('/\D/', '', $data['cpf']));
        // Aqui a mágica do hash acontece automaticamente na criação
        $stmt->bindValue(':hash', password_hash($data['senha_provisoria'], PASSWORD_DEFAULT));
        $stmt->bindValue(':unidade_id', $data['unidade_id'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Usado pelo usuário no primeiro acesso ou troca voluntária
    public function atualizarSenha($usuarioId, $novaSenha)
    {
        // Atualiza o hash e garante que trocar_senha seja 0 (falso)
        $sql = "UPDATE usuarios SET senha_hash = :hash, trocar_senha = 0 WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':hash', password_hash($novaSenha, PASSWORD_DEFAULT));
            $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar senha no banco: " . $e->getMessage());
        }
    }

    /**
     * Busca todos os detalhes do usuário, incluindo perfil e unidade
     */
    public function buscarDadosCompletos(int $id)
    {
        $sql = "SELECT u.*, p.nome as perfil_nome, un.nome as unidade_nome 
                FROM usuarios u 
                JOIN usuario_perfis up ON u.id = up.usuario_id 
                JOIN perfis p ON up.perfil_id = p.id 
                JOIN unidades un ON u.unidade_id = un.id 
                WHERE u.id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar perfil: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista usuários para o DataTables respeitando o RBAC.
     */
    public function listarParaGestao()
    {
        $sql = "SELECT u.id, u.nome, u.cpf, u.ativo, u.trocar_senha, u.ultimo_login, u.unidade_id,
                   p.nome as perfil_nome, un.nome as unidade_nome
            FROM usuarios u
            LEFT JOIN usuario_perfis up ON u.id = up.usuario_id
            LEFT JOIN perfis p ON up.perfil_id = p.id
            LEFT JOIN unidades un ON u.unidade_id = un.id
            WHERE 1=1";

        $params = [];
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            if ($user['perfil'] === 'Gestor Local') {
                $sql .= " AND u.unidade_id = :unid";
                $params[':unid'] = $user['unidade_id'];
            }
            // Admin e Central vêem tudo (conforme planejado)
        }

        $sql .= " ORDER BY u.nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza Unidade e garante a existência/atualização do Perfil
     */
    public function atualizarVinculosAdmin($idUsuario, $idPerfil, $idUnidade)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Atualiza a Unidade na tabela usuarios (Sempre existe)
            $stmtU = $this->conn->prepare("UPDATE usuarios SET unidade_id = :unid WHERE id = :id");
            $stmtU->execute([':unid' => $idUnidade, ':id' => $idUsuario]);

            // 2. Se o perfil foi enviado (Admin logado), usamos a lógica de substituição
            if ($idPerfil !== null) {
                // Deletamos qualquer vínculo anterior para evitar duplicidade ou erro de chave
                $stmtDel = $this->conn->prepare("DELETE FROM usuario_perfis WHERE usuario_id = :id");
                $stmtDel->execute([':id' => $idUsuario]);

                // Inserimos o novo vínculo de perfil
                $stmtIns = $this->conn->prepare("INSERT INTO usuario_perfis (usuario_id, perfil_id) VALUES (:id, :perf)");
                $stmtIns->execute([':id' => $idUsuario, ':perf' => $idPerfil]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw new Exception("Erro ao processar vínculos: " . $e->getMessage());
        }
    }

    /**
     * Alterna o status ativo/inativo
     */
    public function alternarStatus($id, $novoStatus)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET ativo = :status WHERE id = :id");
        return $stmt->execute([':status' => $novoStatus, ':id' => $id]);
    }

    /**
     * Reset de senha pelo Admin
     */
    public function resetarSenha($id, $novaSenhaCriptografada)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET senha_hash = :hash, trocar_senha = 1 WHERE id = :id");
        return $stmt->execute([':hash' => $novaSenhaCriptografada, ':id' => $id]);
    }
}
