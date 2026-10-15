<?php
// components/DAO/RegistroAvariaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';
require_once __DIR__ . '/NotificacaoDAO.php';
require_once __DIR__ . '/ViaturaDAO.php'; // Adicionado para garantir o uso no método inserir

class RegistroAvariaDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

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

        $sql = "SELECT ra.*, v.prefixo, v.numero, 
                   ta.nome_componente, 
                   c.nome AS nome_condutor, 
                   m.status AS status_manutencao
            FROM registro_avaria ra
            JOIN viaturas v ON ra.viatura_id = v.id
            JOIN unidades u ON v.lotacao_atual_id = u.id
            JOIN condutor c ON ra.condutor_id = c.id
            LEFT JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id
            LEFT JOIN manutencoes m ON m.avaria_id = ra.id
            -- ADICIONADO ABAIXO: Necessário para o filtro de pendencia_adm funcionar
            LEFT JOIN procedimentos_apuracao pa ON ra.id = pa.avaria_id
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
        if (!empty($filters['pendencia_adm'])) {
            // Filtra avarias que o Central marcou como obrigatória, mas que o Local ainda não abriu a portaria
            $sql .= " AND ra.requer_apuracao = 1 AND (pa.numero_portaria IS NULL OR pa.numero_portaria = '')";
        }

        $sql .= " ORDER BY ra.data_registro DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserir(array $data)
    {
        // Adicionado o campo cautela_id no INSERT para rastreabilidade
        $sql = "INSERT INTO registro_avaria (viatura_id, tipo_avaria_id, condutor_id, cautela_id, data_registro, observacoes_avaria, requer_apuracao) 
            VALUES (:viatura_id, :tipo_avaria_id, :condutor_id, :cautela_id, :data_registro, :observacoes, :requer_apuracao)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':viatura_id', $data['viatura_id'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_avaria_id', $data['tipo_avaria_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':condutor_id', $data['condutor_id'], PDO::PARAM_INT);
            $stmt->bindValue(':cautela_id', $data['cautela_id'] ?? null, PDO::PARAM_INT); // Vínculo com a cautela
            $stmt->bindValue(':data_registro', $data['data_registro'] ?? date('Y-m-d H:i:s'));
            $stmt->bindValue(':observacoes', $data['observacoes_avaria'], PDO::PARAM_STR);
            $stmt->bindValue(':requer_apuracao', $data['requer_apuracao'] ?? 0, PDO::PARAM_INT);

            $res = $stmt->execute();

            if ($res) {
                // CAPTURA O ID GERADO PELO BANCO DE DADOS
                $novoIdAvaria = (int)$this->conn->lastInsertId();

                // MANTÉM A LÓGICA DE NOTIFICAÇÃO ORIGINAL
                $vDAO = new ViaturaDAO();
                $v = $vDAO->getViaturaById($data['viatura_id']);

                $nDAO = new NotificacaoDAO();
                $nDAO->dispararEvento(
                    (int)$v['lotacao_atual_id'],
                    "Nova Avaria Relatada",
                    "A viatura {$v['prefixo']} {$v['numero']} apresenta um problema.",
                    "avaria",
                    "pages/pesquisar_avaria.php"
                );

                // RETORNO CRÍTICO: Devolve o ID da avaria (50, 51...) e não apenas true/1
                return $novoIdAvaria;
            }

            return false;
        } catch (PDOException $e) {
            throw new Exception("Erro ao registrar avaria: " . $e->getMessage());
        }
    }

    /**
     * Método para salvar apenas o NOME do arquivo na tabela avaria_fotos
     */
    public function inserirFoto(int $avariaId, string $nomeArquivo)
    {
        $sql = "INSERT INTO avaria_fotos (avaria_id, caminho_arquivo) VALUES (:id, :nome)";
        try {
            $stmt = $this->conn->prepare($sql);
            $res = $stmt->execute([':id' => $avariaId, ':nome' => $nomeArquivo]);

            if ($res) {
                // RETORNA O ID REAL GERADO PELO BANCO
                return (int)$this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao inserir foto no banco: " . $e->getMessage());
            return false;
        }
    }

    public function buscarDetalhesPorId(int $id)
    {
        $sql = "SELECT 
        ra.*, 
        v.prefixo, v.numero, v.placa, v.modelo, v.lotacao_atual_id,
        ta.nome_componente, ta.categoria,
        c.nome AS nome_condutor,
        m.status AS status_manutencao,
        m.id AS manutencao_id,
        pa.numero_portaria, pa.status_apuracao, pa.encarregado
    FROM registro_avaria ra
    JOIN viaturas v ON ra.viatura_id = v.id
    LEFT JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id -- ALTERADO PARA LEFT JOIN
    JOIN condutor c ON ra.condutor_id = c.id
    LEFT JOIN manutencoes m ON m.avaria_id = ra.id
    LEFT JOIN procedimentos_apuracao pa ON ra.id = pa.avaria_id
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
        // ADICIONADO: requer_apuracao = :requer_apuracao no SET
        $sql = "UPDATE registro_avaria SET 
                viatura_id = :viatura_id, 
                tipo_avaria_id = :tipo_avaria_id, 
                condutor_id = :condutor_id, 
                data_registro = :data_registro, 
                observacoes_avaria = :observacoes,
                requer_apuracao = :requer_apuracao
            WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':viatura_id', $data['viatura_id'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_avaria_id', $data['tipo_avaria_id'], PDO::PARAM_INT);
            $stmt->bindValue(':condutor_id', $data['condutor_id'], PDO::PARAM_INT);
            $stmt->bindValue(':data_registro', $data['data_registro']);
            $stmt->bindValue(':observacoes', $data['observacoes_avaria'], PDO::PARAM_STR);
            $stmt->bindValue(':requer_apuracao', $data['requer_apuracao'] ?? 0, PDO::PARAM_INT);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar registro de avaria: " . $e->getMessage());
        }
    }

    public function buscarAvariasPorCautela($cautela_id)
    {
        $db = Conexao::getConexao();
        $sql = "SELECT * FROM registro_avaria WHERE cautela_id = :cautela_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['cautela_id' => $cautela_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Inserir ao final da classe RegistroAvariaDAO
    public function listarCautelasConcluidasPorViatura(int $viatura_id): array
    {
        $sql = "SELECT c.id, c.data_fim, c.condutor_id, cond.nome AS condutor_nome 
                FROM cautelas c
                JOIN condutor cond ON c.condutor_id = cond.id
                WHERE c.viatura_id = :viatura_id 
                AND c.status = 'Concluída' 
                ORDER BY c.data_fim DESC 
                LIMIT 10";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':viatura_id', $viatura_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar cautelas concluídas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todas as fotos vinculadas a uma avaria específica
     */
    public function buscarFotos(int $avariaId): array
    {
        $sql = "SELECT id, caminho_arquivo FROM avaria_fotos WHERE avaria_id = :id ORDER BY criado_em DESC";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $avariaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar fotos da avaria: " . $e->getMessage());
            return [];
        }
    }
}
