<?php
// components/Auth/JwtHandler.php
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler
{
    private $secret = "SUA_CHAVE_SECRETA_MUITO_LONGA_E_SEGURA_123"; // Mude isso!
    private $alg = 'HS256';

    // Gera o Token
    public function gerarToken(array $dadosUsuario)
    {
        $issuedAt = time();
        // $expire = $issuedAt + (60 * 60 * 12); // Expira em 12 horas
        $expire = $issuedAt + (60 * 60 * 24 * 7); // Expira em 12 horas

        $payload = [
            "iss" => "smnt_online",       // Emissor
            "iat" => $issuedAt,           // Gerado em
            "exp" => $expire,             // Expira em
            "data" => [                   // Dados do usuário (Claims)
                "id" => $dadosUsuario['id'],
                "nome" => $dadosUsuario['nome'],
                "perfil" => $dadosUsuario['perfil_nome'],
                "perfil_id" => $dadosUsuario['perfil_id'],
                "unidade_id" => $dadosUsuario['unidade_id'],
                "comando_regional_id" => $dadosUsuario['comando_regional_id']
            ]
        ];

        return JWT::encode($payload, $this->secret, $this->alg);
    }

    // Valida o Token
    // public function validarToken($token) {
    //     try {
    //         $decoded = JWT::decode($token, new Key($this->secret, $this->alg));
    //         return (array) $decoded->data;
    //     } catch (Exception $e) {
    //         return false; // Token inválido, expirado ou malformado
    //     }
    // }


    //teste
    public function validarToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->alg));
            return (array) $decoded->data;
        } catch (\Firebase\JWT\ExpiredException $e) {
            // Se cair aqui, o relógio é o culpado
            error_log("Erro JWT: Token Expirado");
            return false;
        } catch (\Exception $e) {
            // Se cair aqui, a chave secreta ou o formato estão errados
            error_log("Erro JWT Geral: " . $e->getMessage());
            return false;
        }
    }
}
