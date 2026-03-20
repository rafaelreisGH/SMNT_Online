<?php
// components/DAO/SearchGlobalDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class SearchGlobalDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    private function getRbacFilter($aliasVtr = 'v', $aliasUnid = 'u')
    {
        $sql = "";
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            if ($user['perfil'] === 'Gestor Local') {
                $sql = " AND {$aliasVtr}.lotacao_atual_id = " . (int)$user['unidade_id'];
            } elseif ($user['perfil'] === 'Gestor Regional') {
                $sql = " AND {$aliasUnid}.comando_regional_id = " . (int)$user['comando_regional_id'];
            }
        }
        return $sql;
    }

    public function buscarGeral(string $termo)
    {
        $termoLike = "%$termo%";
        $results = [];
        $rbacVtr = $this->getRbacFilter('v', 'u');

        try {
            // 1. VIATURAS (Prefixo, Número ou Placa)
            $sqlVtr = "SELECT v.id, v.prefixo, v.numero, v.placa FROM viaturas v
                       JOIN unidades u ON v.lotacao_atual_id = u.id
                       WHERE (v.prefixo LIKE :t OR v.numero LIKE :t OR v.placa LIKE :t) $rbacVtr LIMIT 5";
            $stmt1 = $this->conn->prepare($sqlVtr);
            $stmt1->execute([':t' => $termoLike]);
            while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
                $results[] = [
                    'titulo' => $row['prefixo'] . ' ' . $row['numero'],
                    'subtitulo' => 'Viatura | Placa: ' . $row['placa'],
                    'link' => 'pages/editar_viatura.php?id=' . $row['id'],
                    'icone' => 'fas fa-truck',
                    'cor' => 'text-primary'
                ];
            }

            // 2. CONDUTORES (Nome ou CPF)
            $rbacCond = str_replace('v.lotacao_atual_id', 'c.ubm_id', $rbacVtr);
            $sqlCond = "SELECT c.id, c.nome FROM condutor c
                        JOIN unidades u ON c.ubm_id = u.id
                        WHERE (c.nome LIKE :t OR c.cpf LIKE :t) $rbacCond LIMIT 5";
            $stmt2 = $this->conn->prepare($sqlCond);
            $stmt2->execute([':t' => $termoLike]);
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $results[] = [
                    'titulo' => $row['nome'],
                    'subtitulo' => 'Condutor Habilitado',
                    'link' => 'pages/editar_condutor.php?id=' . $row['id'],
                    'icone' => 'fas fa-user',
                    'cor' => 'text-success'
                ];
            }

            // 3. MANUTENÇÕES (Pelo número da OS)
            if (is_numeric($termo)) {
                $sqlManut = "SELECT m.id, m.ordem_de_servico, v.prefixo FROM manutencoes m
                             JOIN viaturas v ON m.viatura_id = v.id
                             JOIN unidades u ON v.lotacao_atual_id = u.id
                             WHERE m.ordem_de_servico = :os $rbacVtr LIMIT 3";
                $stmt3 = $this->conn->prepare($sqlManut);
                $stmt3->execute([':os' => $termo]);
                while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                    $results[] = [
                        'titulo' => 'OS #' . $row['ordem_de_servico'],
                        'subtitulo' => 'Manutenção Vtr ' . $row['prefixo'],
                        'link' => 'pages/finalizar_manutencao.php?id=' . $row['id'],
                        'icone' => 'fas fa-file-invoice-dollar',
                        'cor' => 'text-warning'
                    ];
                }
            }

            // 4. AVARIAS (Pelo Componente)
            $sqlAv = "SELECT ra.id, ta.nome_componente, v.prefixo FROM registro_avaria ra
                      JOIN viaturas v ON ra.viatura_id = v.id
                      JOIN unidades u ON v.lotacao_atual_id = u.id
                      JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id
                      WHERE ta.nome_componente LIKE :t $rbacVtr LIMIT 3";
            $stmt4 = $this->conn->prepare($sqlAv);
            $stmt4->execute([':t' => $termoLike]);
            while ($row = $stmt4->fetch(PDO::FETCH_ASSOC)) {
                $results[] = [
                    'titulo' => $row['nome_componente'],
                    'subtitulo' => 'Avaria Vtr ' . $row['prefixo'],
                    'link' => 'pages/editar_avaria.php?id=' . $row['id'],
                    'icone' => 'fas fa-exclamation-circle',
                    'cor' => 'text-danger'
                ];
            }

            return $results;
        } catch (PDOException $e) {
            return [];
        }
    }
}
