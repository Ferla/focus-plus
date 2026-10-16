<?php
declare(strict_types=1);
require __DIR__ . '/../../src/db.php';
require __DIR__ . '/../../src/helpers.php';

// CREATE
$pdo = db();
$grupos = $pdo->query('SELECT id_grupo, nombre, periodo FROM grupos ORDER BY nombre')->fetchAll();
$errores = [];
$valores = ['codigo' => '', 'id_grupo' => '', 'promedio_actual' => '', 'promedio_anterior' => '',
            'asistencia_pct' => '', 'actividades_pendientes' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    [$errores, $datos] = validar_estudiante($_POST, $pdo);
    $valores = array_merge($valores, array_intersect_key($_POST, $valores));
    if (!$errores) {
        $st = $pdo->prepare(
            'INSERT INTO estudiantes (codigo, id_grupo, promedio_actual, promedio_anterior, asistencia_pct, actividades_pendientes)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$datos['codigo'], $datos['id_grupo'], $datos['promedio_actual'],
                      $datos['promedio_anterior'], $datos['asistencia_pct'], $datos['actividades_pendientes']]);
        flash('ok', 'Estudiante ' . $datos['codigo'] . ' creado correctamente.');
        redirigir(url('/estudiantes/'));
    }
}

$titulo = 'Nuevo estudiante';
$accion = url('/estudiantes/crear.php');
$boton  = 'Guardar estudiante';
require __DIR__ . '/../../templates/header.php';
?>
<h1>Nuevo estudiante</h1>
<?php require __DIR__ . '/../../templates/form_estudiante.php'; ?>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
