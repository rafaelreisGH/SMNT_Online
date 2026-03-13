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

        // O JOIN no UPDATE garante que a restrição de Unidade/CRBM seja aplicada no momento da escrita
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
                      v.garantia_fim = :garantia_fim
                  WHERE v.id = :id {$rbac['sql']}";

        try {
            $stmt = $this->conn->prepare($query);
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

            foreach ($rbac['params'] as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar viatura: " . $e->getMessage());
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

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao inserir viatura: " . $e->getMessage());
        }
    }

    // Métodos de checagem de unicidade (Globais, sem filtro de unidade por segurança de dados)
    public function existsByPlaca(string $placa, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM viaturas WHERE placa = :placa" . ($excludeId ? " AND id != :ex" : "");
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':placa', $placa);
        if ($excludeId) $stmt->bindValue(':ex', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function existsByRenavam(string $renavam, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM viaturas WHERE renavam = :renavam" . ($excludeId ? " AND id != :ex" : "");
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':renavam', $renavam);
        if ($excludeId) $stmt->bindValue(':ex', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function existsByNumero(int $numero, ?int $excludeId = null): bool {
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
        
        $sql = "SELECT v.id, v.prefixo, v.numero, v.modelo, v.placa 
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
}