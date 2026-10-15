<?php
// api/upload_foto_avaria.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/RegistroAvariaDAO.php';

$response = ['success' => false, 'message' => '', 'fotos' => []];

try {
    $avariaId = filter_input(INPUT_POST, 'avaria_id', FILTER_VALIDATE_INT);
    if (!$avariaId) throw new Exception("ID da avaria inválido.");

    $aDAO = new RegistroAvariaDAO();

    // Validar limite de 5 fotos no servidor
    $fotosAtuais = $aDAO->buscarFotos($avariaId);
    $vagasDisponiveis = 5 - count($fotosAtuais);

    if ($vagasDisponiveis <= 0) throw new Exception("Limite de 5 fotos já atingido.");

    if (isset($_FILES['fotos'])) {
        $dir = "../assets/uploads/avarias/";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $enviadas = 0;
        foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp) {
            if ($enviadas >= $vagasDisponiveis) break;

            if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['fotos']['name'][$key], PATHINFO_EXTENSION);
                $nomeArquivo = "av_" . $avariaId . "_" . time() . "_" . uniqid() . "." . $ext;

                if (move_uploaded_file($tmp, $dir . $nomeArquivo)) {
                    $novoId = $aDAO->inserirFoto($avariaId, $nomeArquivo); // Agora $novoId será o ID real (ex: 85)
                    $response['fotos'][] = [
                        'id' => $novoId,
                        'caminho' => $nomeArquivo
                    ];
                    $enviadas++;
                }
            }
        }
        $response['success'] = true;
        $response['message'] = "$enviadas foto(s) enviada(s) com sucesso!";
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
