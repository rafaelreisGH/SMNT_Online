<?php
// components/Config/EnvLoader.php

/**
 * Carregador simples de variáveis de ambiente a partir do arquivo .env.
 * Substitui a dependência vlucas/phpdotenv, evitando problemas de
 * compatibilidade com PHP 8.4+ e com o sistema de arquivos Windows.
 */
class EnvLoader
{
    /**
     * Carrega as variáveis de ambiente do arquivo .env para $_ENV e putenv().
     * Ignora linhas em branco e comentários (iniciados com #).
     *
     * @param string $path Caminho absoluto do diretório que contém o .env
     * @return void
     */
    public static function load(string $path): void
    {
        $file = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($file) || !is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignora comentários e linhas vazias
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Separa a chave do valor apenas no primeiro '='
            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Remove aspas ao redor do valor, se houver
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $m)) {
                $value = $m[2];
            }

            // Define a variável apenas se ainda não existir no ambiente
            if (!isset($_ENV[$key]) && getenv($key) === false) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}
