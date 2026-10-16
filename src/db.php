<?php
declare(strict_types=1);

/**
 * Devuelve la conexión PDO a MySQL (se crea una sola vez por petición).
 * Usa consultas preparadas en todo el proyecto para evitar inyección SQL.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $ruta = __DIR__ . '/../config/config.php';
        if (!file_exists($ruta)) {
            http_response_code(500);
            exit('Falta config/config.php. Copia config/config.example.php y ajusta tus datos.');
        }
        $cfg = require $ruta;
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $ex) {
            http_response_code(500);
            exit('No se pudo conectar a la base de datos. Revisa config/config.php y que MySQL esté encendido.');
        }
    }
    return $pdo;
}
