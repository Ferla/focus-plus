<?php
declare(strict_types=1);
require __DIR__ . '/../../src/db.php';
require __DIR__ . '/../../src/helpers.php';

// DELETE (solo por POST con token CSRF)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}
csrf_validar();

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false) {
    http_response_code(400);
    exit('Identificador inválido.');
}

$pdo = db();
$st = $pdo->prepare('DELETE FROM estudiantes WHERE id_estudiante = ?');
$st->execute([$id]);

if ($st->rowCount() > 0) {
    flash('ok', 'Estudiante eliminado (sus alertas, intervenciones y seguimientos también).');
} else {
    flash('error', 'No se encontró el estudiante a eliminar.');
}
redirigir(url('/estudiantes/'));
