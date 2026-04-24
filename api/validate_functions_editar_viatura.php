<?php
// ../api/validate_functions_editar_viatura.php

/**
 * Valida os dados de uma viatura (Serve para Cadastro e Edição).
 */
function validateViaturaData(array &$data_in): array
{
    $errors = [];
    $data_out = [];
    $viaturaDAO = new ViaturaDAO();

    // 1. Identificação do Registro (Se houver ID, é Edição. Se não, é Cadastro)
    $id_existente = filter_var($data_in['id'] ?? null, FILTER_VALIDATE_INT) ?: null;

    // 2. Campos Obrigatórios
    if (empty(trim($data_in['tipo_operacional'] ?? ''))) $errors['tipo_operacional'] = "O Tipo Operacional é obrigatório.";
    if (empty(trim($data_in['prefixo'] ?? ''))) $errors['prefixo'] = "O Prefixo é obrigatório.";
    if (empty(trim($data_in['numero'] ?? ''))) $errors['numero'] = "O Número é obrigatório.";
    if (empty(trim($data_in['placa'] ?? ''))) $errors['placa'] = "A Placa é obrigatória.";
    if (empty(trim($data_in['status_da_viatura'] ?? ''))) $errors['status_da_viatura'] = "O Status é obrigatório.";
    if (empty(trim($data_in['lotacao_atual_id'] ?? ''))) $errors['lotacao_atual_id'] = "A Lotação é obrigatória.";

    // 3. Validações de FORMATO
    if (!empty($data_in['ano']) && !is_numeric($data_in['ano'])) $errors['ano'] = "O ano deve conter apenas números.";
    if (!empty($data_in['pbt']) && !is_numeric($data_in['pbt'])) $errors['pbt'] = "O PBT deve conter apenas números.";

    // 4. Validação de UNICIDADE (Essencial para não dar erro de banco)
    // Placa
    $placa_limpa = strtoupper(str_replace(['-', ' '], '', $data_in['placa'] ?? ''));
    if (!empty($placa_limpa) && $viaturaDAO->existsByPlaca($placa_limpa, $id_existente)) {
        $errors['placa'] = "Esta Placa já está cadastrada em outra viatura.";
    }
    // Renavam
    $renavam_limpo = preg_replace('/\D/', '', $data_in['renavam'] ?? '');
    if (!empty($renavam_limpo) && $viaturaDAO->existsByRenavam($renavam_limpo, $id_existente)) {
        $errors['renavam'] = "Este Renavam já está cadastrado em outra viatura.";
    }
    // Número
    $numero_val = filter_var($data_in['numero'] ?? 0, FILTER_VALIDATE_INT);
    if ($numero_val > 0 && $viaturaDAO->existsByNumero($numero_val, $id_existente)) {
        $errors['numero'] = "Este número de viatura já está em uso.";
    }

    // 5. Sanitização Final para o banco (Garantindo NULL onde é opcional)
    $data_out['id'] = $id_existente;
    $data_out['tipo_operacional'] = $data_in['tipo_operacional'] ?? null;
    $data_out['prefixo'] = strtoupper($data_in['prefixo'] ?? '');
    $data_out['numero'] = $numero_val;
    $data_out['placa'] = $placa_limpa;
    $data_out['renavam'] = !empty($renavam_limpo) ? $renavam_limpo : null;
    $data_out['marca'] = !empty($data_in['marca']) ? $data_in['marca'] : null;
    $data_out['modelo'] = !empty($data_in['modelo']) ? $data_in['modelo'] : null;
    $data_out['ano'] = !empty($data_in['ano']) ? (int)$data_in['ano'] : null;
    $data_out['pbt'] = !empty($data_in['pbt']) ? (int)$data_in['pbt'] : null;
    $data_out['tipo_aquisicao'] = $data_in['tipo_aquisicao'] ?? null;
    $data_out['status_da_viatura'] = $data_in['status_da_viatura'] ?? null;
    $data_out['lotacao_atual_id'] = filter_var($data_in['lotacao_atual_id'] ?? 0, FILTER_VALIDATE_INT);
    $data_out['plano_id'] = filter_var($data_in['plano_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $data_out['tanques'] = $data_in['tanques'] ?? []; // Repassa os tanques para o DAO

    // Motorização e Diversos
    $data_out['cilindradas'] = filter_var($data_in['cilindradas'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $data_out['cavalos_forca'] = filter_var($data_in['cavalos_forca'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $data_out['torque'] = $data_in['torque'] ?? null;
    $data_out['quilometragem'] = filter_var($data_in['quilometragem'] ?? 0, FILTER_VALIDATE_INT);
    $data_out['garantia_fim'] = !empty($data_in['garantia_fim']) ? $data_in['garantia_fim'] : null;
    $data_out['tipo_cambio'] = $data_in['tipo_cambio'] ?? null;
    $data_out['numero_marchas'] = filter_var($data_in['numero_marchas'] ?? null, FILTER_VALIDATE_INT) ?: null;

    $data_in = $data_out;
    return $errors;
}
