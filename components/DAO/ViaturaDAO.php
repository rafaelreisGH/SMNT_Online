<?php
// ViaturaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class ViaturaDAO
{
    private $conn;
    private $table_name = "viaturas";

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Helper para aplicar as restrições de escopo (RBAC) nas queries SQL.
     * Retorna um array com a string SQL e os parâmetros necessários.
     */
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
                    // Admin e Gestor Central não recebem filtros (vêem tudo)
            }
        }
        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Busca uma viatura pelo ID respeitando o escopo do usuário.
     */
    public function getViaturaById($id)
    {
        $rbac = $this->aplicarEscopoRBAC();

        $query = "SELECT v.* FROM {$this->table_name} v
                  JOIN unidades u ON v.lotacao_atual_id = u.id
                  WHERE v.id = :id {$rbac['sql']} LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            foreach ($rbac['params'] as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao buscar viatura: " . $e->getMessage());
        }
    }

    /**
     * Atualiza os dados de uma viatura, garantindo que o usuário tenha poder sobre ela via WHERE.
     */
    public function updateViatura(array $data)
    {
        $rbac = $this->aplicarEscopoRBAC();

        $query = "UPDATE {$this->table_name} v
              INNER JOIN unidades u ON v.lotacao_atual_id = u.id
              SET v.tipo_operacional = :tipo_operacional,
                  v.prefixo = :prefixo,
                  v.numero = :numero,
                  v.placa = :placa,
                  v.renavam = :renavam,
                  v.marca = :marca,
                  v.modelo = :modelo,
                  v.ano = :ano,
                  v.pbt = :pbt,
                  v.tipo_aquisicao = :tipo_aquisicao,
                  v.status_da_viatura = :status_da_viatura,
                  v.lotacao_atual_id = :lotacao_atual_id,
                  v.cilindradas = :cilindradas,
                  v.cavalos_forca = :cavalos_forca,
                  v.torque = :torque,
                  v.tipo_cambio = :tipo_cambio,
                  v.numero_marchas = :numero_marchas,
                  v.quilometragem = :quilometragem,
                  v.garantia_fim = :garantia_fim,
                  v.plano_id = :plano_id
              WHERE v.id = :id {$rbac['sql']}";

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare($query);
            // Binds padrão
            $stmt->bindValue(':tipo_operacional', $data['tipo_operacional'] ?? null);
            $stmt->bindValue(':prefixo', $data['prefixo'] ?? null);
            $stmt->bindValue(':numero', $data['numero'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':placa', $data['placa'] ?? null);
            $stmt->bindValue(':renavam', $data['renavam'] ?? null);
            $stmt->bindValue(':marca', $data['marca'] ?? null);
            $stmt->bindValue(':modelo', $data['modelo'] ?? null);
            $stmt->bindValue(':ano', $data['ano'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':pbt', $data['pbt'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_aquisicao', $data['tipo_aquisicao'] ?? null);
            $stmt->bindValue(':status_da_viatura', $data['status_da_viatura'] ?? null);
            $stmt->bindValue(':lotacao_atual_id', $data['lotacao_atual_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':cilindradas', $data['cilindradas'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':cavalos_forca', $data['cavalos_forca'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':torque', $data['torque'] ?: null);
            $stmt->bindValue(':tipo_cambio', $data['tipo_cambio'] ?: null);
            $stmt->bindValue(':numero_marchas', $data['numero_marchas'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':quilometragem', $data['quilometragem'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':garantia_fim', $data['garantia_fim'] ?: null);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
            $stmt->bindValue(':plano_id', !empty($data['plano_id']) ? $data['plano_id'] : null, PDO::PARAM_INT);

            foreach ($rbac['params'] as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }

            // 1. EXECUTA O UPDATE DA VIATURA (Sem o return aqui!)
            $stmt->execute();

            // 2. GESTÃO DE TANQUES (Limpa e Reinserção)
            $sqlDel = "DELETE FROM tanques_combustivel WHERE viatura_id = :v_id";
            $stmtDel = $this->conn->prepare($sqlDel);
            $stmtDel->execute([':v_id' => $data['id']]);

            if (!empty($data['tanques']) && is_array($data['tanques'])) {
                $sqlIns = "INSERT INTO tanques_combustivel (viatura_id, combustivel_id, litragem) VALUES (:v_id, :c_id, :litros)";
                $stmtIns = $this->conn->prepare($sqlIns);

                foreach ($data['tanques'] as $combId => $info) {
                    if (isset($info['ativo']) && $info['ativo'] == '1') {

                        // 1. Tratamento da string (troca vírgula por ponto e converte para float)
                        $litragem = !empty($info['litragem']) ? (float)str_replace(',', '.', $info['litragem']) : 0;

                        // 2. VALIDAÇÃO BACKEND: Impede valores negativos
                        if ($litragem < 0) {
                            // Opção A: Forçar zero se for negativo
                            $litragem = 0;

                            // Opção B (Recomendada): Lançar uma exceção para avisar o erro
                            // throw new Exception("A litragem do combustível não pode ser negativa.");
                        }

                        $stmtIns->execute([
                            ':v_id'   => $data['id'], // ou $viaturaId no caso do insert
                            ':c_id'   => $combId,
                            ':litros' => $litragem
                        ]);
                    }
                }
            }

            // 3. FINALIZA A TRANSAÇÃO
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw new Exception("Erro ao atualizar viatura e tanques: " . $e->getMessage());
        }
    }

    /**
     * Detalhes da viatura com restrição de escopo.
     */
    public function buscarDetalhesViaturaPorId(int $id)
    {
        $rbac = $this->aplicarEscopoRBAC();

        $sql = "SELECT v.*, u.nome as unidade_nome 
                FROM viaturas v
                JOIN unidades u ON v.lotacao_atual_id = u.id
                WHERE v.id = :id_viatura {$rbac['sql']}";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_viatura', $id, PDO::PARAM_INT);
            foreach ($rbac['params'] as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar detalhes: " . $e->getMessage());
        }
    }

    /**
     * Inserção de viatura. 
     * Nota: Aqui o RBAC deve validar se o usuário pode inserir na lotação escolhida.
     */
    public function insertViatura(array $data): bool
    {
        // Validação de Segurança antes do INSERT
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            if ($user['perfil'] === 'Gestor Local' && $data['lotacao_atual_id'] != $user['unidade_id']) {
                throw new Exception("Você só pode cadastrar viaturas para a sua própria unidade.");
            }
            // Adicione lógica similar para Gestor Regional se necessário (checar se unidade pertence ao CRBM)
        }

        $sql = "INSERT INTO viaturas (
            tipo_operacional, prefixo, numero, placa, renavam, marca, modelo, ano, pbt,
            tipo_aquisicao, status_da_viatura, lotacao_atual_id, cilindradas,
            cavalos_forca, torque, quilometragem, garantia_fim, tipo_cambio, numero_marchas
        ) VALUES (
            :tipo_operacional, :prefixo, :numero, :placa, :renavam, :marca, :modelo, :ano, :pbt,
            :tipo_aquisicao, :status_da_viatura, :lotacao_atual_id, :cilindradas,
            :cavalos_forca, :torque, :quilometragem, :garantia_fim, :tipo_cambio, :numero_marchas
        )";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':tipo_operacional', $data['tipo_operacional'] ?? null);
            $stmt->bindValue(':prefixo', $data['prefixo'] ?? null);
            $stmt->bindValue(':numero', $data['numero'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':placa', $data['placa'] ?? null);
            $stmt->bindValue(':renavam', $data['renavam'] ?? null);
            $stmt->bindValue(':marca', $data['marca'] ?? null);
            $stmt->bindValue(':modelo', $data['modelo'] ?? null);
            $stmt->bindValue(':ano', $data['ano'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':pbt', $data['pbt'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_aquisicao', $data['tipo_aquisicao'] ?? null);
            $stmt->bindValue(':status_da_viatura', $data['status_da_viatura'] ?? null);
            $stmt->bindValue(':lotacao_atual_id', $data['lotacao_atual_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':cilindradas', $data['cilindradas'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':cavalos_forca', $data['cavalos_forca'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':torque', $data['torque'] ?: null);
            $stmt->bindValue(':quilometragem', $data['quilometragem'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':garantia_fim', $data['garantia_fim'] ?: null);
            $stmt->bindValue(':tipo_cambio', $data['tipo_cambio'] ?: null);
            $stmt->bindValue(':numero_marchas', $data['numero_marchas'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':plano_id', !empty($data['plano_id']) ? $data['plano_id'] : null, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao inserir viatura: " . $e->getMessage());
        }
    }

    // Métodos de checagem de unicidade (Globais, sem filtro de unidade por segurança de dados)
    public function existsByPlaca(string $placa, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM viaturas WHERE placa = :placa" . ($excludeId ? " AND id != :ex" : "");
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':placa', $placa);
        if ($excludeId) $stmt->bindValue(':ex', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function existsByRenavam(string $renavam, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM viaturas WHERE renavam = :renavam" . ($excludeId ? " AND id != :ex" : "");
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':renavam', $renavam);
        if ($excludeId) $stmt->bindValue(':ex', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function existsByNumero(int $numero, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM viaturas WHERE numero = :numero" . ($excludeId ? " AND id != :ex" : "");
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':numero', $numero, PDO::PARAM_INT);
        if ($excludeId) $stmt->bindValue(':ex', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Listagem para Selects com RBAC aplicado.
     */
    public function listarTodasParaSelect(): array
    {
        $rbac = $this->aplicarEscopoRBAC();

        // ADICIONADO: lotacao_atual_id na consulta
        $sql = "SELECT v.id, v.prefixo, v.numero, v.modelo, v.placa, v.lotacao_atual_id 
            FROM viaturas v
            JOIN unidades u ON v.lotacao_atual_id = u.id
            WHERE 1=1 {$rbac['sql']}
            ORDER BY v.prefixo ASC, v.numero ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($rbac['params'] as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar viaturas: " . $e->getMessage());
        }
    }

    /**
     * Busca os combustíveis e litragens vinculados a uma viatura
     */
    public function buscarTanquesViatura(int $id)
    {
        $sql = "SELECT combustivel_id, litragem FROM tanques_combustivel WHERE viatura_id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            // fetchAll com FETCH_UNIQUE retornará [] se não houver registros
            return $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return []; // Retorna vazio em caso de erro no SQL
        }
    }

    /**
     * Busca viaturas com filtros avançados e suporte a RBAC
     */
    public function buscarViaturasComFiltros(array $f = [])
    {
        $rbac = $this->aplicarEscopoRBAC();
        $params = $rbac['params'];

        // 1. Base da Query com DISTINCT (evita duplicar viatura se tiver 2 tanques)
        $sql = "SELECT DISTINCT 
                v.id, v.prefixo, v.numero, v.marca, v.modelo, v.placa,
                v.status_da_viatura, v.quilometragem,
                u.nome AS unidade_lotacao
            FROM viaturas v
            LEFT JOIN unidades u ON v.lotacao_atual_id = u.id";

        // 2. JOIN condicional para Combustível
        if (!empty($f['combustivel_id'])) {
            $sql .= " INNER JOIN tanques_combustivel tc ON v.id = tc.viatura_id";
        }

        $sql .= " WHERE 1=1 {$rbac['sql']}";

        // 3. Filtros Dinâmicos
        if (!empty($f['tipo_operacional'])) {
            $sql .= " AND v.tipo_operacional = :tipo_op";
            $params[':tipo_op'] = $f['tipo_operacional'];
        }
        if (!empty($f['prefixo'])) {
            $sql .= " AND v.prefixo LIKE :prefixo";
            $params[':prefixo'] = '%' . $f['prefixo'] . '%';
        }
        if (!empty($f['numero'])) {
            $sql .= " AND v.numero = :numero";
            $params[':numero'] = $f['numero'];
        }
        if (!empty($f['placa'])) {
            $sql .= " AND v.placa LIKE :placa";
            $params[':placa'] = '%' . $f['placa'] . '%';
        }
        if (!empty($f['combustivel_id'])) {
            $sql .= " AND tc.combustivel_id = :comb_id";
            $params[':comb_id'] = $f['combustivel_id'];
        }
        if (!empty($f['lotacao_atual_id'])) {
            $sql .= " AND v.lotacao_atual_id = :lotacao";
            $params[':lotacao'] = $f['lotacao_atual_id'];
        }
        if (!empty($f['status_da_viatura'])) {
            $sql .= " AND v.status_da_viatura = :status";
            $params[':status'] = $f['status_da_viatura'];
        }
        if (!empty($f['cilindradas'])) {
            $sql .= " AND v.cilindradas = :cil";
            $params[':cil'] = $f['cilindradas'];
        }
        if (!empty($f['cavalos_forca'])) {
            $sql .= " AND v.cavalos_forca = :cv";
            $params[':cv'] = $f['cavalos_forca'];
        }
        if (!empty($f['tipo_cambio'])) {
            $sql .= " AND v.tipo_cambio = :cambio";
            $params[':cambio'] = $f['tipo_cambio'];
        }
        if (!empty($f['garantia_fim'])) {
            $sql .= " AND v.garantia_fim >= :garantia";
            $params[':garantia'] = $f['garantia_fim'];
        }

        $sql .= " ORDER BY v.prefixo ASC, v.numero ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro SQL: " . $e->getMessage());
        }
    }
}
