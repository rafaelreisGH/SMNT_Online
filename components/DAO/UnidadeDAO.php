<?php
// ARQUIVO: components/DAO/UnidadeDAO.php

require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class UnidadeDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Retorna as unidades com seus respectivos municípios e comandos regionais.
     */
    public function listarAgrupadasPorComando(): array
    {
        $sql = "SELECT 
                    u.id, 
                    u.nome AS unidade_nome, 
                    m.nome AS municipio_nome,
                    COALESCE(c.nome, 'Outros') AS comando_nome
                FROM unidades u
                LEFT JOIN municipios m ON u.municipio_id = m.id
                LEFT JOIN comandos_regionais c ON u.comando_regional_id = c.id
                ORDER BY 
                    (c.nome IS NULL), -- Garante que 'Outros' (nulos) fiquem por último
                    c.nome ASC, 
                    u.nome ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Organiza os dados em um array multidimensional para facilitar o HTML
            $agrupadas = [];
            foreach ($unidades as $unidade) {
                $comando = $unidade['comando_nome'];
                $agrupadas[$comando][] = $unidade;
            }
            
            return $agrupadas;
        } catch (PDOException $e) {
            error_log("Erro no UnidadeDAO (listarAgrupadas): " . $e->getMessage());
            return [];
        }
    }
}