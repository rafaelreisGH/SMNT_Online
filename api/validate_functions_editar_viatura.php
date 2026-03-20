<?php
// ../api/validate_functions_editar_viatura.php

/**
 * Valida os dados de uma viatura.
 */
function validateViaturaData(array &$data_in): array
{
    $errors = [];
    $data_out = [];

    $viaturaDAO = new ViaturaDAO();

    // Validação de campos obrigatórios simples
    if (empty(trim($data_in['tipo_operacional'] ?? ''))) $errors['tipo_operacional'] = "O Tipo Operacional é obrigatório.";
    if (empty(trim($data_in['prefixo'] ?? ''))) $errors['prefixo'] = "O Prefixo é obrigatório.";
    if (empty(trim($data_in['numero'] ?? ''))) $errors['numero'] = "O Número é obrigatório.";
    if (empty(trim($data_in['placa'] ?? ''))) $errors['placa'] = "A Placa é obrigatória.";
    if (empty(trim($data_in['renavam'] ?? ''))) $errors['renavam'] = "O Renavam é obrigatório.";
    if (empty(trim($data_in['marca'] ?? ''))) $errors['marca'] = "A Marca é obrigatória.";
    if (empty(trim($data_in['modelo'] ?? ''))) $errors['modelo'] = "O Modelo é obrigatório.";
    if (empty(trim($data_in['ano'] ?? ''))) $errors['ano'] = "O Ano é obrigatório.";
    if (empty(trim($data_in['pbt'] ?? ''))) $errors['pbt'] = "O PBT é obrigatório.";
    if (empty(trim($data_in['tipo_aquisicao'] ?? ''))) $errors['tipo_aquisicao'] = "O Tipo de Aquisição é obrigatório.";
    if (empty(trim($data_in['status_da_viatura'] ?? ''))) $errors['status_da_viatura'] = "O Status é obrigatório.";
    if (empty(trim($data_in['lotacao_atual_id'] ?? ''))) $errors['lotacao_atual_id'] = "A Lotação é obrigatória.";

    // Sanitização para o banco
    $data_out['id'] = filter_var($data_in['id'] ?? null, FILTER_VALIDATE_INT);
    $data_out['tipo_operacional'] = $data_in['tipo_operacional'] ?? null;
    $data_out['prefixo'] = strtoupper($data_in['prefixo'] ?? '');
    $data_out['numero'] = filter_var($data_in['numero'] ?? 0, FILTER_VALIDATE_INT);
    $data_out['placa'] = strtoupper(str_replace('-', '', $data_in['placa'] ?? ''));
    $data_out['renavam'] = preg_replace('/\D/', '', $data_in['renavam'] ?? '');
    $data_out['marca'] = $data_in['marca'] ?? null;
    $data_out['modelo'] = $data_in['modelo'] ?? null;
    $data_out['ano'] = filter_var($data_in['ano'] ?? 0, FILTER_VALIDATE_INT);
    $data_out['pbt'] = filter_var($data_in['pbt'] ?? 0, FILTER_VALIDATE_INT);
    $data_out['tipo_aquisicao'] = $data_in['tipo_aquisicao'] ?? null;
    $data_out['status_da_viatura'] = $data_in['status_da_viatura'] ?? null;
    $data_out['lotacao_atual_id'] = filter_var($data_in['lotacao_atual_id'] ?? 0, FILTER_VALIDATE_INT);
    $data_out['plano_id'] = filter_var($data_in['plano_id'] ?? null, FILTER_VALIDATE_INT) ?: null;

    // Campos Opcionais
    $data_out['cilindradas'] = filter_var($data_in['cilindradas'] ?? '', FILTER_VALIDATE_INT) ?: null;
    $data_out['cavalos_forca'] = filter_var($data_in['cavalos_forca'] ?? '', FILTER_VALIDATE_INT) ?: null;
    $data_out['torque'] = $data_in['torque'] ?? null;
    $data_out['quilometragem'] = filter_var($data_in['quilometragem'] ?? 0, FILTER_VALIDATE_INT);
    $data_out['garantia_fim'] = !empty($data_in['garantia_fim']) ? $data_in['garantia_fim'] : null;
    $data_out['tipo_cambio'] = $data_in['tipo_cambio'] ?? null;
    $data_out['numero_marchas'] = filter_var($data_in['numero_marchas'] ?? '', FILTER_VALIDATE_INT) ?: null;

    $data_in = $data_out;
    return $errors;
}
