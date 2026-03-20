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
        $sql = "UPDATE usuarios SET senha_hash = :hash, trocar_senha = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':hash', password_hash($novaSenha, PASSWORD_DEFAULT));
        $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
        return $stmt->execute();
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
}
