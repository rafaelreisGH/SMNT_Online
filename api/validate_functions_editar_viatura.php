<?php
// ../api/validate_functions_editar_viatura.php

/**
 * Valida os dados de uma viatura.
 *
 * @param array $data Os dados da viatura vindos do formulário (POST).
 * @return array Um array associativo de erros. Se vazio, significa que a validação passou.
 */
function validateViaturaData(array &$data_in): array
{
    $errors = [];
    $data_out = []; // Array para armazenar dados sanitizados e validados

    // Instancia o ViaturaDAO para realizar as verificações de unicidade
    $viaturaDAO = new ViaturaDAO();

    // ID da viatura (para edição, pode ser null/ausente no cadastro)
    $data_out['id'] = filter_var($data_in['id'] ?? null, FILTER_VALIDATE_INT);
    // No cadastro, o ID será null, então não deve ser validado como obrigatório aqui.
    // A validação de ID como obrigatório é responsabilidade da lógica em editar_viatura.php.

    // 1. tipo_operacional (Obrigatório, Enum)
    $valid_tipos_operacionais = ['Operacional Geral', 'Combate a incendio', 'Administrativa'];
    $data_out['tipo_operacional'] = filter_var($data_in['tipo_operacional'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['tipo_operacional'])) {
        $errors['tipo_operacional'] = "O Tipo Operacional é obrigatório.";
    } elseif (!in_array($data_out['tipo_operacional'], $valid_tipos_operacionais)) {
        $errors['tipo_operacional'] = "Tipo Operacional inválido.";
    }

    // 2. prefixo (Obrigatório, String, Max 5 caracteres)
    $data_out['prefixo'] = filter_var($data_in['prefixo'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $data_out['prefixo'] = strtoupper($data_out['prefixo']); // Converte para maiúsculas
    if (empty($data_out['prefixo'])) {
        $errors['prefixo'] = "O Prefixo é obrigatório.";
    } elseif (strlen($data_out['prefixo']) > 5) {
        $errors['prefixo'] = "O Prefixo não pode ter mais de 5 caracteres.";
    }

    // 3. numero (Obrigatório, Inteiro)
    $data_out['numero'] = filter_var($data_in['numero'] ?? '', FILTER_VALIDATE_INT);
    if ($data_out['numero'] === false || $data_out['numero'] === null) {
        $errors['numero'] = "O Número é obrigatório e deve ser um número inteiro.";
    } else {
        // VERIFICAÇÃO DE UNICIDADE PARA NÚMERO
        try {
            // Se o ID for válido (edição), exclua-o da verificação
            $excludeId = ($data_out['id'] !== false && $data_out['id'] !== null) ? $data_out['id'] : null;
            if ($viaturaDAO->existsByNumero($data_out['numero'], $excludeId)) {
                $errors['numero'] = "Já existe uma viatura com este número.";
            }
        } catch (Exception $e) {
            $errors['numero'] = "Erro ao verificar a unicidade do número: " . $e->getMessage();
            error_log("Erro na validação de unicidade (Número): " . $e->getMessage());
        }
    }

    // 4. placa (Obrigatório, String, Exatamente 7 caracteres, formato AAA0000 ou AAA0A00)
    $data_out['placa'] = filter_var($data_in['placa'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $data_out['placa'] = strtoupper(str_replace('-', '', $data_out['placa'])); // Remove hífen e converte para maiúsculas
    // Padrão de placa Mercosul (3 letras, 1 número, 1 letra, 2 números) ou antiga (3 letras, 4 números)
    $placa_pattern = '/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/';
    if (empty($data_out['placa'])) {
        $errors['placa'] = "A Placa é obrigatória.";
    } elseif (!preg_match($placa_pattern, $data_out['placa'])) {
        $errors['placa'] = "Formato de placa inválido. Use 3 letras, 1 número, 1 letra/número, 2 números (ex: ABC1E23).";
    } else {
        // VERIFICAÇÃO DE UNICIDADE PARA PLACA
        try {
            $excludeId = ($data_out['id'] !== false && $data_out['id'] !== null) ? $data_out['id'] : null;
            if ($viaturaDAO->existsByPlaca($data_out['placa'], $excludeId)) {
                $errors['placa'] = "Já existe uma viatura com esta placa.";
            }
        } catch (Exception $e) {
            $errors['placa'] = "Erro ao verificar a unicidade da placa: " . $e->getMessage();
            error_log("Erro na validação de unicidade (Placa): " . $e->getMessage());
        }
    }

    // 5. renavam (Obrigatório, String, Exatamente 11 números)
    $data_out['renavam'] = filter_var($data_in['renavam'] ?? '', FILTER_SANITIZE_NUMBER_INT); // Apenas números
    if (empty($data_out['renavam'])) {
        $errors['renavam'] = "O Renavam é obrigatório.";
    } elseif (!preg_match('/^\d{11}$/', $data_out['renavam'])) {
        $errors['renavam'] = "O Renavam deve conter exatamente 11 números.";
    } else {
        // VERIFICAÇÃO DE UNICIDADE PARA RENAVAM
        try {
            $excludeId = ($data_out['id'] !== false && $data_out['id'] !== null) ? $data_out['id'] : null;
            if ($viaturaDAO->existsByRenavam($data_out['renavam'], $excludeId)) {
                $errors['renavam'] = "Já existe uma viatura com este Renavam.";
            }
        } catch (Exception $e) {
            $errors['renavam'] = "Erro ao verificar a unicidade do Renavam: " . $e->getMessage();
            error_log("Erro na validação de unicidade (Renavam): " . $e->getMessage());
        }
    }

    // 6. marca (Obrigatório, String, Max 50 caracteres)
    $data_out['marca'] = filter_var($data_in['marca'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['marca'])) {
        $errors['marca'] = "A Marca é obrigatória.";
    } elseif (strlen($data_out['marca']) > 50) {
        $errors['marca'] = "A Marca não pode ter mais de 50 caracteres.";
    }

    // 7. modelo (Obrigatório, String, Max 50 caracteres)
    $data_out['modelo'] = filter_var($data_in['modelo'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['modelo'])) {
        $errors['modelo'] = "O Modelo é obrigatório.";
    } elseif (strlen($data_out['modelo']) > 50) {
        $errors['modelo'] = "O Modelo não pode ter mais de 50 caracteres.";
    }

    // 8. ano (Obrigatório, Inteiro, entre 1900 e ano atual + 1)
    $data_out['ano'] = filter_var($data_in['ano'] ?? '', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1900, 'max_range' => (int)date('Y') + 1]
    ]);
    if ($data_out['ano'] === false || $data_out['ano'] === null) {
        $errors['ano'] = "O Ano é obrigatório e deve ser um número válido.";
    }

    // 9. pbt (Obrigatório, Inteiro)
    $data_out['pbt'] = filter_var($data_in['pbt'] ?? '', FILTER_VALIDATE_INT);
    if ($data_out['pbt'] === false || $data_out['pbt'] === null) {
        $errors['pbt'] = "O PBT é obrigatório e deve ser um número inteiro.";
    }

    // 10. tipo_aquisicao (Obrigatório, Enum)
    $valid_tipos_aquisicao = ['Própria', 'Doação', 'Fiel Depositário', 'Locada'];
    // CORREÇÃO: Aplicar trim() para limpar espaços em branco
    $data_out['tipo_aquisicao'] = trim(filter_var($data_in['tipo_aquisicao'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    if (empty($data_out['tipo_aquisicao'])) {
        $errors['tipo_aquisicao'] = "O Tipo de Aquisição é obrigatório.";
    } elseif (!in_array($data_out['tipo_aquisicao'], $valid_tipos_aquisicao)) {
        $errors['tipo_aquisicao'] = "Tipo de Aquisição inválido.";
    }

    // 11. status_da_viatura (Obrigatório, Enum)
    $valid_status_viatura = ['Operante', 'Baixada', 'Descarregada', 'Indisponível'];
    $data_out['status_da_viatura'] = filter_var($data_in['status_da_viatura'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['status_da_viatura'])) {
        $errors['status_da_viatura'] = "O Status da Viatura é obrigatório.";
    } elseif (!in_array($data_out['status_da_viatura'], $valid_status_viatura)) {
        $errors['status_da_viatura'] = "Status da Viatura inválido.";
    }

    // 12. lotacao_atual_id (Obrigatório, Inteiro)
    $data_out['lotacao_atual_id'] = filter_var($data_in['lotacao_atual_id'] ?? '', FILTER_VALIDATE_INT);
    if ($data_out['lotacao_atual_id'] === false || $data_out['lotacao_atual_id'] === null) {
        $errors['lotacao_atual_id'] = "A Lotação Atual é obrigatória e deve ser um número inteiro (ID da unidade).";
    }

    // 13. cilindradas (Opcional, Inteiro)
    $data_out['cilindradas'] = filter_var($data_in['cilindradas'] ?? '', FILTER_VALIDATE_INT);
    if ($data_out['cilindradas'] === false && ($data_in['cilindradas'] ?? '') !== '') {
        $errors['cilindradas'] = "Cilindradas deve ser um número inteiro válido ou vazio.";
    }
    // Converter para null se for vazio (o filter_var com '' resulta em false)
    if (($data_in['cilindradas'] ?? '') === '') {
        $data_out['cilindradas'] = null;
    }


    // 14. cavalos_forca (Opcional, Inteiro)
    $data_out['cavalos_forca'] = filter_var($data_in['cavalos_forca'] ?? '', FILTER_VALIDATE_INT);
    if ($data_out['cavalos_forca'] === false && ($data_in['cavalos_forca'] ?? '') !== '') {
        $errors['cavalos_forca'] = "Cavalos de Força deve ser um número inteiro válido ou vazio.";
    }
    if (($data_in['cavalos_forca'] ?? '') === '') {
        $data_out['cavalos_forca'] = null;
    }

    // 15. torque (Opcional, String, Max 50 caracteres)
    $data_out['torque'] = filter_var($data_in['torque'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (strlen($data_out['torque']) > 50) {
        $errors['torque'] = "Torque não pode ter mais de 50 caracteres.";
    }
    if (empty($data_out['torque'])) {
        $data_out['torque'] = null;
    }

    // 16. quilometragem (Opcional, Inteiro)
    $data_out['quilometragem'] = filter_var($data_in['quilometragem'] ?? '', FILTER_VALIDATE_INT);
    if ($data_out['quilometragem'] === false && ($data_in['quilometragem'] ?? '') !== '') {
        $errors['quilometragem'] = "Quilometragem deve ser um número inteiro válido ou vazio.";
    }
    if (($data_in['quilometragem'] ?? '') === '') {
        $data_out['quilometragem'] = null;
    }

    // 17. garantia_fim (Opcional, Data YYYY-MM-DD)
    $data_out['garantia_fim'] = filter_var($data_in['garantia_fim'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (!empty($data_out['garantia_fim']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_out['garantia_fim'])) {
        $errors['garantia_fim'] = "O Fim da Garantia deve ser uma data válida no formato AAAA-MM-DD.";
    }
    if (empty($data_out['garantia_fim'])) {
        $data_out['garantia_fim'] = null;
    }

    // 18. tipo_cambio (Opcional, Enum)
    $valid_tipos_cambio = ['Manual', 'Automático'];
    // CORREÇÃO: Aplicar trim() para limpar espaços em branco
    $data_out['tipo_cambio'] = trim(filter_var($data_in['tipo_cambio'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    if (!empty($data_out['tipo_cambio']) && !in_array($data_out['tipo_cambio'], $valid_tipos_cambio)) {
        $errors['tipo_cambio'] = "Tipo de Câmbio inválido.";
    }
    if (empty($data_out['tipo_cambio'])) {
        $data_out['tipo_cambio'] = null;
    }

    // 19. numero_marchas (Opcional, Inteiro, min 0)
    $data_out['numero_marchas'] = filter_var($data_in['numero_marchas'] ?? '', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0] // Pode ser 0 para carros automáticos, por exemplo
    ]);
    if ($data_out['numero_marchas'] === false && ($data_in['numero_marchas'] ?? '') !== '') {
        $errors['numero_marchas'] = "Número de Marchas deve ser um número inteiro válido ou vazio.";
    }
    if (($data_in['numero_marchas'] ?? '') === '') {
        $data_out['numero_marchas'] = null;
    }

    // ATENÇÃO: Os campos abaixo NÃO ESTÃO NO SEU SCHEMA DE TABELA 'VIATURAS'
    // Se você pretende salvá-los, precisará ADICIONÁ-LOS à sua tabela 'viaturas' e ao DAO.
    // Enquanto não estiverem no BD, eles serão apenas ignorados no insertViatura.
    // Se você quiser que o formulário valide-os, remova o unset correspondente no cadastro.php
    // e adicione validação aqui (e remova o unset no cadastro.php).

    // tipo_carroceria (Opcional, String, Max 100 caracteres)
    $data_out['tipo_carroceria'] = filter_var($data_in['tipo_carroceria'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (strlen($data_out['tipo_carroceria']) > 100) {
        $errors['tipo_carroceria'] = "Tipo de Carroceria não pode ter mais de 100 caracteres.";
    }
    if (empty($data_out['tipo_carroceria'])) {
        $data_out['tipo_carroceria'] = null;
    }

    // equipamentos_especificos (Opcional, String)
    $data_out['equipamentos_especificos'] = filter_var($data_in['equipamentos_especificos'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // Adicione validação de comprimento se necessário
    if (empty($data_out['equipamentos_especificos'])) {
        $data_out['equipamentos_especificos'] = null;
    }

    // Atualiza o array $data_in com os dados sanitizados para que o chamador possa usá-los
    $data_in = $data_out;

    return $errors;
}
