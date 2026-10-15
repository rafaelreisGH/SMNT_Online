<?php
// components/Log/LoggerService.php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

// Handler customizado para gravar no nosso Banco de Dados
class MySQLHandler extends AbstractProcessingHandler
{
    private $pdo;

    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        $this->pdo = Conexao::getConexao();
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        // Extrai dados contextuais enviados no log
        $c = $record->context;

        $sql = "INSERT INTO logs_atividades (usuario_id, acao, tabela, registro_id, detalhes, ip) 
        VALUES (:u_id, :acao, :tab, :reg, :det, :ip)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':u_id' => $c['usuario_id'] ?? 0,
            ':acao' => $record->message,
            ':tab'  => $c['tabela'] ?? null,
            ':reg'  => $c['registro_id'] ?? null,
            ':det'  => $c['detalhes'] ?? null,
            ':ip'   => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}

class LoggerService
{
    private static $logger;

    public static function get()
    {
        if (!self::$logger) {
            self::$logger = new Logger('smnt_logger');
            self::$logger->pushHandler(new MySQLHandler());
        }
        return self::$logger;
    }

    /**
     * Atalho para registrar atividade
     */
    public static function registrar($acao, $tabela = null, $regId = null, $detalhes = null)
    {
        if (defined('USER_AUTH')) {
            self::get()->info($acao, [
                'usuario_id'  => USER_AUTH['id'],
                'tabela'      => $tabela,
                'registro_id' => $regId,
                'detalhes'    => $detalhes
            ]);
        }
    }
}
