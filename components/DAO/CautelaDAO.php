<?php
// components/DAO/CautelaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class CautelaDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Abre uma nova cautela (Saída da Viatura)
     * @param array $dados - Campos da tabela 'cautelas'
     * @param array $itens - Lista de acessórios (id e status entregue)
     */

    public function abrirCautela(array $dados, array $itens)
    {
        try {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn->beginTransaction();

            $sql = "INSERT INTO cautelas (
                    viatura_id, condutor_id, gestor_entrega_id, 
                    data_inicio, odometro_saida, combustivel_saida, 
                    motivo_deslocamento, status
                ) VALUES (
                    :v_id, :c_id, :g_id, 
                    NOW(), :odo, :comb, 
                    :motivo, :status
                )";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':v_id'   => $dados['viatura_id'],
                ':c_id'   => $dados['condutor_id'],
                ':g_id'   => USER_AUTH['id'],
                ':odo'    => $dados['odometro_saida'],
                ':comb'   => $dados['combustivel_saida'],
                // ALTERAÇÃO AQUI: Garante string vazia em vez de null se o campo falhar
                ':motivo' => isset($dados['motivo_deslocamento']) ? (string)$dados['motivo_deslocamento'] : '',
                ':status' => $dados['status'] ?? 'Pendente'
            ]);

            $cautelaId = $this->conn->lastInsertId();

            // Gravando itens do checklist
            $sqlItem = "INSERT INTO cautela_itens (cautela_id, acessorio_id, entregue_saida) VALUES (:c_id, :a_id, :status)";
            $stmtItem = $this->conn->prepare($sqlItem);

            foreach ($itens as $item) {
                // IMPORTANTE: Removemos qualquer 'if' aqui. 
                // Gravamos o que o JS mandou (0 ou 1).
                $stmtItem->execute([
                    ':c_id'   => $cautelaId,
                    ':a_id'   => $item['id'],
                    ':status' => (int)$item['status'] // Força o valor vindo do JS
                ]);
            }

            $this->conn->commit();
            return $cautelaId;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            // ALTERAÇÃO AQUI: Lança o erro real para a API capturar
            throw new Exception("Erro no Banco de Dados: " . $e->getMessage());
        }
    }

    /**
     * Atualiza uma cautela existente (Apenas se status for Pendente)
     */
    public function atualizarCautela(array $dados, array $itens)
    {
        try {
            $this->conn->beginTransaction();

            $sql = "UPDATE cautelas SET 
                    viatura_id = :v_id, condutor_id = :c_id, 
                    odometro_saida = :odo, combustivel_saida = :comb, 
                    motivo_deslocamento = :motivo, status = :status
                WHERE id = :id AND status = 'Pendente'";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':v_id'   => $dados['viatura_id'],
                ':c_id'   => $dados['condutor_id'],
                ':odo'    => $dados['odometro_saida'],
                ':comb'   => $dados['combustivel_saida'],
                ':motivo' => $dados['motivo_deslocamento'] ?: null,
                ':status' => $dados['status'] ?? 'Pendente',
                ':id'     => $dados['id']
            ]);

            // Atualiza checklist: Remove os antigos e insere os novos (mais simples e seguro)
            $this->conn->prepare("DELETE FROM cautela_itens WHERE cautela_id = :id")->execute([':id' => $dados['id']]);

            $sqlItem = "INSERT INTO cautela_itens (cautela_id, acessorio_id, entregue_saida) VALUES (:c_id, :a_id, :status)";
            $stmtItem = $this->conn->prepare($sqlItem);

            foreach ($itens as $item) {
                $stmtItem->execute([
                    ':c_id'   => $dados['id'], // ID que veio do form
                    ':a_id'   => $item['id'],
                    ':status' => $item['status']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Busca cautelas respeitando o RBAC
     */
    public function buscarCautelas(array $f = [])
    {
        // Reutilize a lógica de RBAC (se já tiver o método aplicarEscopoRBAC no CautelaDAO)
        // Se não tiver, use a lógica direta:
        $sql_rbac = "";
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            if ($user['perfil'] === 'Gestor Local') {
                $sql_rbac = " AND v.lotacao_atual_id = " . (int)$user['unidade_id'];
            } elseif ($user['perfil'] === 'Gestor Regional') {
                $sql_rbac = " AND u.comando_regional_id = " . (int)$user['comando_regional_id'];
            } elseif ($user['perfil'] === 'Condutor') {
                // O condutor só vê as dele (IMPORTANTE)
                $sql_rbac = " AND c.condutor_id = (SELECT id FROM condutor WHERE usuario_id = " . (int)$user['id'] . ")";
            }
        }

        $sql = "SELECT c.*, v.prefixo, v.numero, cond.nome as condutor_nome, u.nome as unidade_nome
        FROM cautelas c
        JOIN viaturas v ON c.viatura_id = v.id
        JOIN condutor cond ON c.condutor_id = cond.id
        JOIN unidades u ON v.lotacao_atual_id = u.id
        WHERE 1=1 $sql_rbac";

        // Aqui você aplicaria o aplicarEscopoRBAC() que já temos nos outros DAOs

        if (!empty($f['status'])) {
            $sql .= " AND c.status = :status";
        }

        $sql .= " ORDER BY c.data_inicio DESC";
        $stmt = $this->conn->prepare($sql);
        if (!empty($f['status'])) $stmt->bindValue(':status', $f['status']);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca avarias preexistentes para exibir no termo de cautela
     */
    public function buscarAvariasAtivas(int $viaturaId)
    {
        // Busca avarias da viatura que não possuem uma manutenção com status 'Concluída'
        $sql = "SELECT ra.*, ta.nome_componente 
                FROM registro_avaria ra
                JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id
                WHERE ra.viatura_id = :v_id 
                AND ra.id NOT IN (
                    SELECT avaria_id FROM manutencoes WHERE status = 'Concluída' AND avaria_id IS NOT NULL
                )
                ORDER BY ra.data_registro DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':v_id' => $viaturaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza apenas o status de uma cautela
     */
    public function atualizarStatus(int $id, string $novoStatus)
    {
        $sql = "UPDATE cautelas SET status = :status WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':status', $novoStatus);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar status da cautela: " . $e->getMessage());
        }
    }

    /**
     * Busca o checklist de uma cautela específica
     */
    public function buscarChecklist(int $cautelaId)
    {
        // entregue_saida contém o 0 ou 1
        $sql = "SELECT acessorio_id, entregue_saida FROM cautela_itens WHERE cautela_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $cautelaId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Busca uma cautela específica pelo ID para edição ou visualização
     */
    public function getCautelaById(int $id)
    {
        $rbac = $this->aplicarEscopoRBAC();

        $sql = "SELECT c.*, v.prefixo, v.numero, cond.nome as condutor_nome
            FROM cautelas c
            JOIN viaturas v ON c.viatura_id = v.id
            JOIN unidades u ON v.lotacao_atual_id = u.id
            JOIN condutor cond ON c.condutor_id = cond.id
            WHERE c.id = :id {$rbac['sql']}";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // ADICIONADO: Vincula os parâmetros do RBAC (unidade_id, crbm_id ou user_id)
            foreach ($rbac['params'] as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar cautela por ID: " . $e->getMessage());
        }
    }

    /**
     * Helper para aplicar as restrições de escopo (RBAC) nas queries SQL.
     */
    private function aplicarEscopoRBAC(string $aliasViatura = 'v', string $aliasUnidade = 'u', string $aliasCautela = 'c'): array
    {
        $sql = "";
        $params = [];

        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            switch ($user['perfil']) {
                case 'Gestor Local':
                    // Filtra por viaturas da própria unidade
                    $sql = " AND {$aliasViatura}.lotacao_atual_id = :rbac_unidade_id";
                    $params[':rbac_unidade_id'] = $user['unidade_id'];
                    break;
                case 'Gestor Regional':
                    // Filtra por unidades do mesmo Comando Regional
                    $sql = " AND {$aliasUnidade}.comando_regional_id = :rbac_crbm_id";
                    $params[':rbac_crbm_id'] = $user['comando_regional_id'];
                    break;
                case 'Condutor':
                    // Condutor só visualiza cautelas onde ELE é o responsável
                    $sql = " AND {$aliasCautela}.condutor_id = (SELECT id FROM condutor WHERE usuario_id = :rbac_user_id)";
                    $params[':rbac_user_id'] = $user['id'];
                    break;
            }
        }
        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Realiza a assinatura digital da cautela (Aceite do Condutor)
     */
    public function assinarCautela(int $id, int $usuarioId)
    {
        $sql = "UPDATE cautelas SET 
                status = 'Ativa', 
                assinatura_saida_data = NOW() 
            WHERE id = :id AND status = 'Disponível'";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao processar assinatura no banco: " . $e->getMessage());
        }
    }

    /**
     * Busca todos os dados para emissão do Termo de Cautela (PDF/Modal)
     */
    public function buscarDadosCompletosDocumento(int $id)
    {
        $sql = "SELECT c.*, 
                   v.prefixo, v.numero, v.placa, v.marca, v.modelo,
                   cond.nome as condutor_nome, cond.cpf as condutor_cpf,
                   un.nome as unidade_nome,
                   gest.nome as gestor_entrega_nome
            FROM cautelas c
            JOIN viaturas v ON c.viatura_id = v.id
            JOIN unidades un ON v.lotacao_atual_id = un.id
            JOIN condutor cond ON c.condutor_id = cond.id
            JOIN usuarios gest ON c.gestor_entrega_id = gest.id
            WHERE c.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
