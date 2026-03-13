<?php
// ../api/validate_functions_editar_viatura.php

require_once 'middleware_auth.php';
/**
 * Valida os dados de uma viatura.
 *
 * @param array $data Os dados da viatura vindos do formulário (POST).
 * @return array Um array associativo de erros. Se vazio, significa que a validação passou.
 */
function validateViaturaData(array &$data_in): array
{
    $errors = [];
    $data_out = []; 

    $viaturaDAO = new ViaturaDAO();

    $data_out['id'] = filter_var($data_in['id'] ?? null, FILTER_VALIDATE_INT);

    // 1. tipo_operacional
    $valid_tipos_operacionais = ['Operacional', 'Administrativo'];
    $data_out['tipo_operacional'] = filter_var($data_in['tipo_operacional'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['tipo_operacional'])) {
        $errors['tipo_operacional'] = "O Tipo Operacional é obrigatório.";
    } elseif (!in_array($data_out['tipo_operacional'], $valid_tipos_operacionais)) {
        $errors['tipo_operacional'] = "Tipo Operacional inválido.";
    }

    // 2. prefixo
    $data_out['prefixo'] = filter_var($data_in['prefixo'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $data_out['prefixo'] = strtoupper($data_out['prefixo']); 
    if (empty($data_out['prefixo'])) {
        $errors['prefixo'] = "O Prefixo é obrigatório.";
    } elseif (strlen($data_out['prefixo']) > 5) {
        $errors['prefixo'] = "O Prefixo não pode ter mais de 5 caracteres.";
    }

    // 3. número
    $data_out['numero'] = filter_var($data_in['numero'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($data_out['numero'] === false || $data_out['numero'] === null) {
        $errors['numero'] = "O Número é obrigatório e deve ser um número inteiro positivo.";
    } else {
        try {
            $excludeId = ($data_out['id'] !== false && $data_out['id'] !== null) ? $data_out['id'] : null;
            if ($viaturaDAO->existsByNumero($data_out['numero'], $excludeId)) {
                $errors['numero'] = "Já existe uma viatura com este número.";
            }
        } catch (Exception $e) {
            $errors['numero'] = "Erro ao verificar a unicidade do número.";
        }
    }

    // 4. placa
    $data_out['placa'] = filter_var($data_in['placa'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $data_out['placa'] = strtoupper(str_replace('-', '', $data_out['placa']));
    $placa_pattern = '/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/';
    if (empty($data_out['placa'])) {
        $errors['placa'] = "A Placa é obrigatória.";
    } elseif (!preg_match($placa_pattern, $data_out['placa'])) {
        $errors['placa'] = "Formato de placa inválido.";
    } else {
        try {
            $excludeId = ($data_out['id'] !== false && $data_out['id'] !== null) ? $data_out['id'] : null;
            if ($viaturaDAO->existsByPlaca($data_out['placa'], $excludeId)) {
                $errors['placa'] = "Já existe uma viatura com esta placa.";
            }
        } catch (Exception $e) {
            $errors['placa'] = "Erro ao verificar a unicidade da placa.";
        }
    }

    // 5. RENAVAM (AJUSTADO: Máximo de 11 dígitos, permitindo menores para veículos antigos)
    // Remove qualquer caractere que não seja número
    $renavam_limpo = preg_replace('/\D/', '', $data_in['renavam'] ?? '');
    
    if (empty($renavam_limpo)) {
        $errors['renavam'] = "O Renavam é obrigatório.";
    } elseif (strlen($renavam_limpo) > 11) {
        $errors['renavam'] = "O Renavam não pode ter mais de 11 números.";
    } else {
        // Se passar na contagem, salvamos o valor limpo
        $data_out['renavam'] = $renavam_limpo;
        
        // VERIFICAÇÃO DE UNICIDADE PARA RENAVAM
        try {
            $excludeId = ($data_out['id'] !== false && $data_out['id'] !== null) ? $data_out['id'] : null;
            if ($viaturaDAO->existsByRenavam($data_out['renavam'], $excludeId)) {
                $errors['renavam'] = "Já existe uma viatura com este Renavam.";
            }
        } catch (Exception $e) {
            $errors['renavam'] = "Erro ao verificar a unicidade do Renavam.";
        }
    }

    // 6. marca
    $data_out['marca'] = filter_var($data_in['marca'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['marca'])) {
        $errors['marca'] = "A Marca é obrigatória.";
    }

    // 7. modelo
    $data_out['modelo'] = filter_var($data_in['modelo'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['modelo'])) {
        $errors['modelo'] = "O Modelo é obrigatório.";
    }

    // 8. ano
    $data_out['ano'] = filter_var($data_in['ano'] ?? '', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1900, 'max_range' => (int)date('Y') + 1]
    ]);
    if ($data_out['ano'] === false || $data_out['ano'] === null) {
        $errors['ano'] = "O Ano é obrigatório e deve ser um número válido.";
    }

    // 9. pbt
    $data_out['pbt'] = filter_var($data_in['pbt'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($data_out['pbt'] === false || $data_out['pbt'] === null) {
        $errors['pbt'] = "O PBT é obrigatório e deve ser um número inteiro positivo.";
    }

    // 10. tipo_aquisicao
    $valid_tipos_aquisicao = ['Própria', 'Doação', 'Fiel Depositário', 'Locada'];
    $valor_aquisicao = isset($data_in['tipo_aquisicao']) ? trim($data_in['tipo_aquisicao']) : '';
    if (empty($valor_aquisicao)) {
        $errors['tipo_aquisicao'] = "O Tipo de Aquisição é obrigatório.";
    } elseif (!in_array($valor_aquisicao, $valid_tipos_aquisicao)) {
        $errors['tipo_aquisicao'] = "Tipo de Aquisição inválido.";
    } else {
        $data_out['tipo_aquisicao'] = $valor_aquisicao;
    }

    // 11. status_da_viatura
    $valid_status_viatura = ['Operante', 'Baixada', 'Descarregada', 'Indisponível'];
    $data_out['status_da_viatura'] = filter_var($data_in['status_da_viatura'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['status_da_viatura'])) {
        $errors['status_da_viatura'] = "O Status da Viatura é obrigatório.";
    } elseif (!in_array($data_out['status_da_viatura'], $valid_status_viatura)) {
        $errors['status_da_viatura'] = "Status da Viatura inválido.";
    }

    // 12. lotacao_atual_id
    $data_out['lotacao_atual_id'] = filter_var($data_in['lotacao_atual_id'] ?? '', FILTER_VALIDATE_INT);
    if ($data_out['lotacao_atual_id'] === false || $data_out['lotacao_atual_id'] === null) {
        $errors['lotacao_atual_id'] = "A Lotação Atual é obrigatória.";
    }

    // 13. cilindradas
    $data_out['cilindradas'] = filter_var($data_in['cilindradas'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($data_out['cilindradas'] === false && ($data_in['cilindradas'] ?? '') !== '') {
        $errors['cilindradas'] = "Cilindradas inválida.";
    }
    if (($data_in['cilindradas'] ?? '') === '') $data_out['cilindradas'] = null;

    // 14. cavalos_forca
    $data_out['cavalos_forca'] = filter_var($data_in['cavalos_forca'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($data_out['cavalos_forca'] === false && ($data_in['cavalos_forca'] ?? '') !== '') {
        $errors['cavalos_forca'] = "Cavalos de Força inválido.";
    }
    if (($data_in['cavalos_forca'] ?? '') === '') $data_out['cavalos_forca'] = null;

    // 15. torque
    $data_out['torque'] = filter_var($data_in['torque'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($data_out['torque'])) $data_out['torque'] = null;

    // 16. quilometragem
    $data_out['quilometragem'] = filter_var($data_in['quilometragem'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($data_out['quilometragem'] === false && ($data_in['quilometragem'] ?? '') !== '') {
        $errors['quilometragem'] = "Quilometragem inválida.";
    }
    if (($data_in['quilometragem'] ?? '') === '') $data_out['quilometragem'] = null;

    // 17. garantia_fim
    $data_out['garantia_fim'] = filter_var($data_in['garantia_fim'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (!empty($data_out['garantia_fim']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_out['garantia_fim'])) {
        $errors['garantia_fim'] = "Data de garantia inválida.";
    }
    if (empty($data_out['garantia_fim'])) $data_out['garantia_fim'] = null;

    // 18. tipo_cambio
    $valid_tipos_cambio = ['Manual', 'Automático'];
    $data_out['tipo_cambio'] = trim(filter_var($data_in['tipo_cambio'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    if (!empty($data_out['tipo_cambio']) && !in_array($data_out['tipo_cambio'], $valid_tipos_cambio)) {
        $errors['tipo_cambio'] = "Tipo de Câmbio inválido.";
    }
    if (empty($data_out['tipo_cambio'])) $data_out['tipo_cambio'] = null;

    // 19. numero_marchas
    $data_out['numero_marchas'] = filter_var($data_in['numero_marchas'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if (($data_in['numero_marchas'] ?? '') === '') $data_out['numero_marchas'] = null;

    $data_in = $data_out;
    return $errors;
}