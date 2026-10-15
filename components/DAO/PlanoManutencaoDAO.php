<?php
// components/DAO/PlanoManutencaoDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class PlanoManutencaoDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    public function listarPlanos()
    {
        $sql = "SELECT * FROM planos_manutencao ORDER BY descricao ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarItensPreventiva()
    {
        $sql = "SELECT * FROM itens_preventiva ORDER BY nome ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserirPlanoComRegras(string $descricao, array $regras)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Insere o Plano
            $stmt = $this->conn->prepare("INSERT INTO planos_manutencao (descricao) VALUES (:desc)");
            $stmt->execute([':desc' => $descricao]);
            $planoId = $this->conn->lastInsertId();

            // 2. Insere as Regras vinculadas
            $sqlRegra = "INSERT INTO regras_manutencao (plano_id, item_id, periodicidade_km, periodicidade_horas, periodicidade_meses) 
                         VALUES (:p_id, :i_id, :km, :hrs, :meses)";
            $stmtRegra = $this->conn->prepare($sqlRegra);

            foreach ($regras as $r) {
                $stmtRegra->execute([
                    ':p_id'  => $planoId,
                    ':i_id'  => $r['item_id'],
                    ':km'    => !empty($r['km']) ? $r['km'] : null,
                    ':hrs'   => !empty($r['horas']) ? $r['horas'] : null,
                    ':meses' => !empty($r['meses']) ? $r['meses'] : null
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Lista todos os planos com a contagem de regras vinculadas
     */
    public function listarTodos(): array
    {
        $sql = "SELECT p.*, 
            (SELECT COUNT(*) FROM regras_manutencao WHERE plano_id = p.id) as total_regras 
            FROM planos_manutencao p 
            ORDER BY p.descricao ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca as regras detalhadas de um plano específico
     */
    public function buscarRegrasDoPlano(int $planoId): array
    {
        $sql = "SELECT r.*, i.nome as item_nome 
            FROM regras_manutencao r
            JOIN itens_preventiva i ON r.item_id = i.id
            WHERE r.plano_id = :plano_id
            ORDER BY i.nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':plano_id' => $planoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
