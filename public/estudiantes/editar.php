<?php
declare(strict_types=1);
require __DIR__ . '/../../src/db.php';
require __DIR__ . '/../../src/helpers.php';

// UPDATE
$pdo = db();
$id = filter_var($_GET['id'] ?? $_POST['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false) {
    http_response_code(400);
    exit('Identificador inválido.');
}

$st = $pdo->prepare('SELECT * FROM estudiantes WHERE id_estudiante = ?');
$st->execute([$id]);
$est = $st->fetch();
if (!$est) {
    http_response_code(404);
    exit('Estudiante no encontrado.');
}

$grupos = $pdo->query('SELECT id_grupo, nombre, periodo FROM grupos ORDER BY nombre')->fetchAll();
$errores = [];
$valores = [
    'codigo' => $est['codigo'], 'id_grupo' => $est['id_grupo'],
    'promedio_actual' => $est['promedio_actual'], 'promedio_anterior' => $est['promedio_anterior'],
    'asistencia_pct' => $est['asistencia_pct'], 'actividades_pendientes' => $est['actividades_pendientes'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    [$errores, $datos] = validar_estudiante($_POST, $pdo, $id);
    $valores = array_merge($valores, array_intersect_key($_POST, $valores));
    if (!$errores) {
        $up = $pdo->prepare(
            'UPDATE estudiantes
             SET codigo = ?, id_grupo = ?, promedio_actual = ?, promedio_anterior = ?,
                 asistencia_pct = ?, actividades_pendientes = ?
             WHERE id_estudiante = ?'
        );
        $up->execute([$datos['codigo'], $datos['id_grupo'], $datos['promedio_actual'],
                      $datos['promedio_anterior'], $datos['asistencia_pct'],
                      $datos['actividades_pendientes'], $id]);
        flash('ok', 'Estudiante ' . $datos['codigo'] . ' actualizado.');
        redirigir(url('/estudiantes/'));
    }
}

$titulo = 'Editar estudiante';
$accion = url('/estudiantes/editar.php') . '?id=' . $id;
$boton  = 'Guardar cambios';
require __DIR__ . '/../../templates/header.php';
?>
<h1>Editar estudiante <?= e($est['codigo']) ?></h1>
<?php require __DIR__ . '/../../templates/form_estudiante.php'; ?>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
