<?php
// components/DAO/LogDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class LogDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Busca os logs do banco aplicando as restrições de perfil (RBAC)
     */
    public function buscarLogs()
    {
        // Base da consulta
        $sql = "SELECT l.*, u.nome as usuario_nome, p.nome as perfil_nome, un.nome as unidade_nome
                FROM logs_atividades l
                JOIN usuarios u ON l.usuario_id = u.id
                JOIN usuario_perfis up ON u.id = up.usuario_id
                JOIN perfis p ON up.perfil_id = p.id
                JOIN unidades un ON u.unidade_id = un.id
                WHERE 1=1";

        $params = [];

        // Aplicação do RBAC (Escopo de visualização)
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            if ($user['perfil'] === 'Gestor Local') {
                $sql .= " AND u.unidade_id = :unid";
                $params[':unid'] = $user['unidade_id'];
            } elseif ($user['perfil'] === 'Gestor Regional') {
                $sql .= " AND un.comando_regional_id = :crbm";
                $params[':crbm'] = $user['comando_regional_id'];
            }
        }

        $sql .= " ORDER BY l.criado_em DESC LIMIT 100";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar logs: " . $e->getMessage());
            return [];
        }
    }
}
