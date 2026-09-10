<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Classe de gerenciamento da conexão com o banco de dados MySQL via PDO.
 * As credenciais de acesso são carregadas dinamicamente do arquivo de configuração (.env).
 */
class Conexao
{
    private static $conn;

    /**
     * Retorna a instância única da conexão PDO (Padrão Singleton).
     *
     * @return PDO Objeto de conexão PDO com o MySQL
     */
    public static function getConexao()
    {
        if (!self::$conn) {
            // Carrega as variáveis de ambiente do arquivo .env na raiz do projeto
            $envPath = __DIR__ . '/../../';
            if (file_exists($envPath . '.env')) {
                $dotenv = Dotenv::createImmutable($envPath);
                $dotenv->safeLoad();
            }

            $servername = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? null;
            $myDB       = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? null;
            $username   = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? null;
            $password   = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? null;

            try {
                self::$conn = new PDO("mysql:host=$servername;dbname=$myDB;charset=utf8mb4", $username, $password);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log("Connection failed: " . $e->getMessage(), 3, "errors.log");
                die("Erro ao conectar ao banco de dados.");
            }
        }

        return self::$conn;
    }
}

