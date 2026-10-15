<?php
// components/DAO/ApuracaoDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class ApuracaoDAO
{
    private $conn;
    private $table_name = "procedimentos_apuracao";

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Busca os dados da apuração vinculados a uma avaria
     */
    public function buscarPorAvariaId(int $avariaId)
    {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE avaria_id = :avaria_id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':avaria_id', $avariaId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar procedimento de apuração: " . $e->getMessage());
        }
    }

    /**
     * Insere ou Atualiza um procedimento (Lógica 1:1)
     */
    public function salvar(array $data): bool
    {
        // Verifica se já existe para decidir entre INSERT ou UPDATE
        $existente = $this->buscarPorAvariaId($data['avaria_id']);

        if ($existente) {
            $sql = "UPDATE " . $this->table_name . " SET 
                        numero_portaria = :num,
                        data_abertura = :data_ab,
                        encarregado = :enc,
                        status_apuracao = :status,
                        resultado = :res
                    WHERE avaria_id = :avaria_id";
        } else {
            $sql = "INSERT INTO " . $this->table_name . " 
                    (avaria_id, numero_portaria, data_abertura, encarregado, status_apuracao, resultado) 
                    VALUES (:avaria_id, :num, :data_ab, :enc, :status, :res)";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':avaria_id', $data['avaria_id'], PDO::PARAM_INT);
            $stmt->bindValue(':num', !empty($data['numero_portaria']) ? $data['numero_portaria'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':data_ab', !empty($data['data_abertura']) ? $data['data_abertura'] : null);
            $stmt->bindValue(':enc', !empty($data['encarregado']) ? $data['encarregado'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':status', $data['status_apuracao'] ?? 'Em andamento', PDO::PARAM_STR);
            $stmt->bindValue(':res', !empty($data['resultado']) ? $data['resultado'] : null, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar dados da apuração: " . $e->getMessage());
        }
    }
}
