<?php
declare(strict_types=1);
require __DIR__ . '/../../src/db.php';
require __DIR__ . '/../../src/helpers.php';

// READ (detalle) con alerta explicable: factores, valores y umbrales
$pdo = db();
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false) {
    http_response_code(400);
    exit('Identificador inválido.');
}
$st = $pdo->prepare('SELECT e.*, g.nombre AS grupo FROM estudiantes e JOIN grupos g ON g.id_grupo = e.id_grupo WHERE e.id_estudiante = ?');
$st->execute([$id]);
$est = $st->fetch();
if (!$est) {
    http_response_code(404);
    exit('Estudiante no encontrado.');
}
$calc = calcular_nivel($est);
$clase = strtolower(str_replace(' ', '-', $calc['nivel']));
$titulo = 'Detalle ' . $est['codigo'];
require __DIR__ . '/../../templates/header.php';
?>
<div class="encabezado">
  <h1>Estudiante <?= e($est['codigo']) ?></h1>
  <span class="nivel nivel-<?= e($clase) ?>">Nivel: <?= e($calc['nivel']) ?></span>
</div>

<dl class="detalle">
  <dt>Grupo</dt><dd><?= e($est['grupo']) ?></dd>
  <dt>Promedio actual</dt><dd><?= e(number_format((float) $est['promedio_actual'], 2)) ?></dd>
  <dt>Promedio anterior</dt><dd><?= e(number_format((float) $est['promedio_anterior'], 2)) ?></dd>
  <dt>Asistencia</dt><dd><?= e(number_format((float) $est['asistencia_pct'], 1)) ?> %</dd>
  <dt>Actividades pendientes</dt><dd><?= (int) $est['actividades_pendientes'] ?></dd>
</dl>

<h2>¿Por qué este nivel?</h2>
<?php if ($calc['factores']): ?>
  <ul>
    <?php foreach ($calc['factores'] as $f): ?><li><?= e($f) ?></li><?php endforeach; ?>
  </ul>
  <p class="nota">Reglas: descenso ≤ −0,5 · asistencia &lt; 80 % · 3 o más pendientes.
     La alerta la valida una persona; el sistema no decide por ella.</p>
<?php else: ?>
  <p>No se cumple ninguna regla de alerta.</p>
<?php endif; ?>

<div class="acciones">
  <a class="btn btn-primario" href="<?= e(url('/estudiantes/editar.php')) ?>?id=<?= (int) $est['id_estudiante'] ?>">Editar</a>
  <a class="btn" href="<?= e(url('/estudiantes/')) ?>">Volver al listado</a>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
