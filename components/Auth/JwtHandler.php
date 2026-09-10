<?php
// components/Auth/JwtHandler.php
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

/**
 * Classe responsável por gerenciar a emissão e validação de tokens JWT.
 * Utiliza variáveis de ambiente (.env) para carregar a chave secreta de assinatura.
 */
class JwtHandler
{
    private $secret;
    private $alg = 'HS256';

    public function __construct()
    {
        // Carrega as variáveis de ambiente do arquivo .env na raiz do projeto
        $envPath = __DIR__ . '/../../';
        if (file_exists($envPath . '.env')) {
            $dotenv = Dotenv::createImmutable($envPath);
            $dotenv->safeLoad();
        }

        // Define a chave secreta a partir das variáveis de ambiente
        $this->secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?? 'CHAVE_PADRAO_SEGURANCA_CBMMT_2026';
    }

    /**
     * Gera um token JWT assinado para o usuário autenticado.
     *
     * @param array $dadosUsuario Dados do usuário autenticado (id, nome, perfil, etc.)
     * @return string Token JWT gerado e assinado
     */
    public function gerarToken(array $dadosUsuario)
    {
        $issuedAt = time();
        // Expiração definida para 8 horas: 60s * 60m * 8h
        $expire = $issuedAt + (60 * 60 * 8);

        $payload = [
            "iss" => "smnt_online",       // Emissor
            "iat" => $issuedAt,           // Gerado em
            "exp" => $expire,             // Expira em 8 horas
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

    /**
     * Valida um token JWT recebido e retorna seus dados.
     *
     * @param string $token String do token JWT recebido no cabeçalho Authorization
     * @return array|false Dados do usuário contidos no payload ou false se inválido/expirado
     */
    public function validarToken($token)
    {
        try {
            // Registro de erro usando o logger padronizado
            $logger = LoggerFactory::createLogger();
            $logger->info('Token JWT expirado', [
                'id' => $_SESSION['id'] ?? null,
                'usuario' => $_SESSION['nome'] ?? null,
                'email' => $_SESSION['email'] ?? null,
                'perfil' => $_SESSION['nivel_de_acesso'] ?? null,
                'sujeito' => null
            ]);
            return false;
        } catch (\Exception $e) {
            // Registro de erro geral usando o logger padronizado
            $logger = LoggerFactory::createLogger();
            $logger->info('Erro JWT geral', [
                'id' => $_SESSION['id'] ?? null,
                'usuario' => $_SESSION['nome'] ?? null,
                'email' => $_SESSION['email'] ?? null,
                'perfil' => $_SESSION['nivel_de_acesso'] ?? null,
                'sujeito' => null,
                'mensagem' => $e->getMessage()
            ]);
            return false;
    }
}

