<?php
// ViaturaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php'; 

class ViaturaDAO {
    private $conn;
    private $table_name = "viaturas"; // Ajuste para o nome real da sua tabela

    public function __construct() {
        // Usa sua classe Conexao para obter a conexão PDO
        // Se a conexão falhar aqui, Conexao::getConexao() já chama die(), parando o script.
        $this->conn = Conexao::getConexao();
    }

    /**
     * Busca uma viatura pelo ID.
     * @param int $id O ID da viatura.
     * @return array|false Retorna os dados da viatura como array associativo ou false se não encontrada.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function getViaturaById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Especifique o tipo para segurança
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Em vez de apenas logar, relança uma exceção para ser tratada pela camada superior (cadastro.php/editar_viatura.php)
            throw new Exception("Erro de PDO ao buscar viatura: " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            throw new Exception("Erro inesperado ao buscar viatura: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Atualiza os dados de uma viatura.
     * @param array $data Um array associativo com os dados da viatura (id, placa, renavam, etc.).
     * @return bool True se a atualização for bem-sucedida, false caso contrário.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function updateViatura(array $data) {
        // Ajuste esta query com TODOS os campos da sua tabela que você quer atualizar.
        $query = "UPDATE " . $this->table_name . " SET
                            tipo_operacional = :tipo_operacional,
                            prefixo = :prefixo,
                            numero = :numero,
                            placa = :placa,
                            renavam = :renavam,
                            marca = :marca,
                            modelo = :modelo,
                            ano = :ano,
                            pbt = :pbt,
                            tipo_aquisicao = :tipo_aquisicao,
                            status_da_viatura = :status_da_viatura,
                            lotacao_atual_id = :lotacao_atual_id,
                            cilindradas = :cilindradas,
                            cavalos_forca = :cavalos_forca,
                            torque = :torque,
                            tipo_cambio = :tipo_cambio,
                            numero_marchas = :numero_marchas,
                            quilometragem = :quilometragem,
                            garantia_fim = :garantia_fim
                      WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);

            // Bind de TODOS os parâmetros, com seus respectivos tipos PDO se possível
            // Usando o operador de coalescência nula (??) para garantir que valores vazios virem null para o banco.
            $stmt->bindValue(':tipo_operacional', $data['tipo_operacional'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':prefixo', $data['prefixo'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':numero', $data['numero'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':placa', $data['placa'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':renavam', $data['renavam'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':marca', $data['marca'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':modelo', $data['modelo'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':ano', $data['ano'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':pbt', $data['pbt'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_aquisicao', $data['tipo_aquisicao'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':status_da_viatura', $data['status_da_viatura'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':lotacao_atual_id', $data['lotacao_atual_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':cilindradas', $data['cilindradas'] !== '' ? $data['cilindradas'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':cavalos_forca', $data['cavalos_forca'] !== '' ? $data['cavalos_forca'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':torque', $data['torque'] !== '' ? $data['torque'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':tipo_cambio', $data['tipo_cambio'] !== '' ? $data['tipo_cambio'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':numero_marchas', $data['numero_marchas'] !== '' ? $data['numero_marchas'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':quilometragem', $data['quilometragem'] !== '' ? $data['quilometragem'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':garantia_fim', $data['garantia_fim'] !== '' ? $data['garantia_fim'] : null, PDO::PARAM_STR); // Datas como string 'YYYY-MM-DD'
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT); // ID sempre presente na atualização

            return $stmt->execute();
        } catch (PDOException $e) {
            // Relança uma exceção para ser tratada pela camada superior (editar_viatura.php)
            throw new Exception("Erro de PDO ao atualizar viatura: " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            // Relança uma exceção genérica
            throw new Exception("Erro inesperado ao atualizar viatura: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Busca os detalhes completos de uma viatura pelo seu ID para exibição detalhada.
     * @param int $id O ID da viatura a ser buscada.
     * @return array|false Retorna um array associativo com os dados detalhados da viatura ou false se não encontrada.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function buscarDetalhesViaturaPorId(int $id) {
        $sql = "SELECT
                    v.id,
                    v.placa,
                    v.prefixo,
                    v.marca,
                    v.modelo,
                    v.ano,
                    v.quilometragem,
                    v.tipo_operacional,
                    v.status_da_viatura,
                    v.tipo_cambio,
                    v.numero_marchas,
                    v.numero,
                    v.cilindradas,
                    v.cavalos_forca,
                    v.torque,
                    v.tipo_aquisicao,
                    v.renavam,
                    v.pbt,
                    v.garantia_fim,
                    v.lotacao_atual_id -- Adicionado para buscar e exibir a lotação atual (ID)
                FROM
                    viaturas v
                WHERE
                    v.id = :id_viatura";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_viatura', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao buscar detalhes da viatura: " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            throw new Exception("Erro inesperado ao buscar detalhes da viatura: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Insere uma nova viatura no banco de dados.
     *
     * @param array $data Um array associativo contendo os dados da nova viatura.
     * Os campos 'id' (se auto-incremento) devem ser omitidos.
     * @return bool Retorna true em caso de sucesso na inserção, false em caso de falha.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function insertViatura(array $data): bool {
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

            // Vinculação de parâmetros (bindValue)
            // Use PDO::PARAM_NULL para campos que podem ser nulos no banco de dados (se o valor for null/vazio)
            // e PDO::PARAM_STR/PDO::PARAM_INT para os outros, conforme o tipo.
            // O operador ?? null garante que se a chave não existir ou for null, o valor será null.
            // A verificação !== '' ? ... : null garante que strings vazias sejam convertidas para null.

            $stmt->bindValue(':tipo_operacional', $data['tipo_operacional'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':prefixo', $data['prefixo'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':numero', $data['numero'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':placa', $data['placa'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':renavam', $data['renavam'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':marca', $data['marca'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':modelo', $data['modelo'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':ano', $data['ano'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':pbt', $data['pbt'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_aquisicao', $data['tipo_aquisicao'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':status_da_viatura', $data['status_da_viatura'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':lotacao_atual_id', $data['lotacao_atual_id'] ?? null, PDO::PARAM_INT); // Chave estrangeira

            // Campos opcionais de motorização e diversos (podem ser null)
            $stmt->bindValue(':cilindradas', $data['cilindradas'] !== '' ? $data['cilindradas'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':cavalos_forca', $data['cavalos_forca'] !== '' ? $data['cavalos_forca'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':torque', $data['torque'] !== '' ? $data['torque'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':quilometragem', $data['quilometragem'] !== '' ? $data['quilometragem'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':garantia_fim', $data['garantia_fim'] !== '' ? $data['garantia_fim'] : null, PDO::PARAM_STR); // Data como string 'YYYY-MM-DD'
            $stmt->bindValue(':tipo_cambio', $data['tipo_cambio'] !== '' ? $data['tipo_cambio'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':numero_marchas', $data['numero_marchas'] !== '' ? $data['numero_marchas'] : null, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            // Relança uma exceção para ser tratada pela camada superior (cadastro.php)
            throw new Exception("Erro de PDO ao inserir viatura: " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            // Relança uma exceção genérica
            throw new Exception("Erro inesperado ao inserir viatura: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Verifica se uma placa já existe no banco de dados, excluindo um ID específico (para edição).
     * @param string $placa A placa a ser verificada.
     * @param int|null $excludeId O ID da viatura a ser excluída da verificação (para atualizações).
     * @return bool True se a placa já existe, false caso contrário.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function existsByPlaca(string $placa, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE placa = :placa";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':placa', $placa, PDO::PARAM_STR);
            if ($excludeId !== null) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao verificar placa: " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            throw new Exception("Erro inesperado ao verificar placa: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Verifica se um renavam já existe no banco de dados, excluindo um ID específico (para edição).
     * @param string $renavam O renavam a ser verificado.
     * @param int|null $excludeId O ID da viatura a ser excluída da verificação (para atualizações).
     * @return bool True se o renavam já existe, false caso contrário.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function existsByRenavam(string $renavam, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE renavam = :renavam";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':renavam', $renavam, PDO::PARAM_STR);
            if ($excludeId !== null) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao verificar renavam: " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            throw new Exception("Erro inesperado ao verificar renavam: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Verifica se um número de viatura já existe no banco de dados, excluindo um ID específico (para edição).
     * @param int $numero O número a ser verificado.
     * @param int|null $excludeId O ID da viatura a ser excluída da verificação (para atualizações).
     * @return bool True se o número já existe, false caso contrário.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function existsByNumero(int $numero, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE numero = :numero";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
            if ($excludeId !== null) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao verificar número da viatura: " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            throw new Exception("Erro inesperado ao verificar número da viatura: " . $e->getMessage(), 0, $e);
        }
    }
}