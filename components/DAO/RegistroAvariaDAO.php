<?php
// components/DAO/RegistroAvariaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';
require_once __DIR__ . '/NotificacaoDAO.php';

class RegistroAvariaDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    // Helper para restrição de visualização por perfil
    private function aplicarEscopoRBAC(string $aliasViatura = 'v', string $aliasUnidade = 'u'): array
    {
        $sql = "";
        $params = [];
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            switch ($user['perfil']) {
                case 'Gestor Local':
                    $sql = " AND {$aliasViatura}.lotacao_atual_id = :rbac_unidade_id";
                    $params[':rbac_unidade_id'] = $user['unidade_id'];
                    break;
                case 'Gestor Regional':
                    $sql = " AND {$aliasUnidade}.comando_regional_id = :rbac_crbm_id";
                    $params[':rbac_crbm_id'] = $user['comando_regional_id'];
                    break;
            }
        }
        return ['sql' => $sql, 'params' => $params];
    }

    public function listarTiposAvaria()
    {
        $sql = "SELECT id, nome_componente, categoria FROM tipo_avaria ORDER BY categoria, nome_componente";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarAvarias(array $filters): array
    {
        $rbac = $this->aplicarEscopoRBAC();

        $sql = "SELECT ra.*, v.prefixo, v.numero, ta.nome_componente, c.nome AS nome_condutor, m.status AS status_manutencao
                FROM registro_avaria ra
                JOIN viaturas v ON ra.viatura_id = v.id
                JOIN unidades u ON v.lotacao_atual_id = u.id
                JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id
                JOIN condutor c ON ra.condutor_id = c.id
                LEFT JOIN manutencoes m ON m.avaria_id = ra.id
                WHERE 1=1 {$rbac['sql']}";

        $params = $rbac['params'];

        if (!empty($filters['viatura_id'])) {
            $sql .= " AND ra.viatura_id = :v_id";
            $params[':v_id'] = $filters['viatura_id'];
        }
        if (!empty($filters['condutor_id'])) {
            $sql .= " AND ra.condutor_id = :c_id";
            $params[':c_id'] = $filters['condutor_id'];
        }
        if (!empty($filters['tipo_avaria_id'])) {
            $sql .= " AND ra.tipo_avaria_id = :ta_id";
            $params[':ta_id'] = $filters['tipo_avaria_id'];
        }
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND ra.data_registro >= :d_ini";
            $params[':d_ini'] = $filters['data_inicio'] . " 00:00:00";
        }
        if (!empty($filters['data_fim'])) {
            $sql .= " AND ra.data_registro <= :d_fim";
            $params[':d_fim'] = $filters['data_fim'] . " 23:59:59";
        }

        $sql .= " ORDER BY ra.data_registro DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserir(array $data)
    {
        $sql = "INSERT INTO registro_avaria (viatura_id, tipo_avaria_id, condutor_id, data_registro, observacoes_avaria) 
            VALUES (:viatura_id, :tipo_avaria_id, :condutor_id, :data_registro, :observacoes)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':viatura_id', $data['viatura_id'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_avaria_id', $data['tipo_avaria_id'], PDO::PARAM_INT);
            $stmt->bindValue(':condutor_id', $data['condutor_id'], PDO::PARAM_INT);
            $stmt->bindValue(':data_registro', $data['data_registro']);
            $stmt->bindValue(':observacoes', $data['observacoes_avaria'], PDO::PARAM_STR);

            // 1. Executa a inserção da avaria primeiro
            $res = $stmt->execute();

            // 2. Se salvou no banco, agora disparamos a notificação
            if ($res) {
                // Buscamos os dados da viatura para a mensagem não ficar "vazia"
                $vDAO = new ViaturaDAO();
                $v = $vDAO->getViaturaById($data['viatura_id']);

                // Instanciamos o NotificacaoDAO para enviar os alertas
                require_once __DIR__ . '/NotificacaoDAO.php';
                $nDAO = new NotificacaoDAO();

                $nDAO->dispararEvento(
                    (int)$v['lotacao_atual_id'],
                    "Nova Avaria Relatada",
                    "A viatura {$v['prefixo']} {$v['numero']} apresenta um problema.",
                    "avaria",
                    "pages/pesquisar_avaria.php"
                );
            }

            // 3. Só agora retornamos o resultado para a API
            return $res;
        } catch (PDOException $e) {
            throw new Exception("Erro ao registrar avaria: " . $e->getMessage());
        }
    }

    public function buscarDetalhesPorId(int $id)
    {
        $sql = "SELECT 
                ra.*, 
                v.prefixo, v.numero, v.placa, v.modelo,
                ta.nome_componente, ta.categoria,
                c.nome AS nome_condutor,
                m.status AS status_manutencao,
                m.id AS manutencao_id
            FROM registro_avaria ra
            JOIN viaturas v ON ra.viatura_id = v.id
            JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id
            JOIN condutor c ON ra.condutor_id = c.id
            LEFT JOIN manutencoes m ON m.avaria_id = ra.id
            WHERE ra.id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar detalhes da avaria: " . $e->getMessage());
        }
    }

    public function atualizar(array $data): bool
    {
        $sql = "UPDATE registro_avaria SET 
                viatura_id = :viatura_id, 
                tipo_avaria_id = :tipo_avaria_id, 
                condutor_id = :condutor_id, 
                data_registro = :data_registro, 
                observacoes_avaria = :observacoes 
            WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':viatura_id', $data['viatura_id'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_avaria_id', $data['tipo_avaria_id'], PDO::PARAM_INT);
            $stmt->bindValue(':condutor_id', $data['condutor_id'], PDO::PARAM_INT);
            $stmt->bindValue(':data_registro', $data['data_registro']);
            $stmt->bindValue(':observacoes', $data['observacoes_avaria'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar registro de avaria: " . $e->getMessage());
        }
    }
}
