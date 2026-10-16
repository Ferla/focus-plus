<?php
declare(strict_types=1);
require __DIR__ . '/../../src/db.php';
require __DIR__ . '/../../src/helpers.php';

// READ (listado) con búsqueda por código y filtro por grupo
$pdo = db();
$q      = trim((string) ($_GET['q'] ?? ''));
$grupo  = filter_var($_GET['grupo'] ?? '', FILTER_VALIDATE_INT);

$sql = 'SELECT e.*, g.nombre AS grupo
        FROM estudiantes e
        JOIN grupos g ON g.id_grupo = e.id_grupo
        WHERE 1 = 1';
$par = [];
if ($q !== '') {
    $sql .= ' AND e.codigo LIKE ?';
    $par[] = '%' . $q . '%';
}
if ($grupo !== false) {
    $sql .= ' AND e.id_grupo = ?';
    $par[] = $grupo;
}
$sql .= ' ORDER BY e.codigo';

$st = $pdo->prepare($sql);
$st->execute($par);
$estudiantes = $st->fetchAll();
$grupos = $pdo->query('SELECT id_grupo, nombre FROM grupos ORDER BY nombre')->fetchAll();

$titulo = 'Estudiantes';
require __DIR__ . '/../../templates/header.php';
?>
<div class="encabezado">
  <h1>Estudiantes</h1>
  <a class="btn btn-primario" href="<?= e(url('/estudiantes/crear.php')) ?>">+ Nuevo estudiante</a>
</div>

<form method="get" class="filtros">
  <input type="text" name="q" placeholder="Buscar por código…" value="<?= e($q) ?>">
  <select name="grupo">
    <option value="">Todos los grupos</option>
    <?php foreach ($grupos as $g): ?>
      <option value="<?= (int) $g['id_grupo'] ?>" <?= $grupo === (int) $g['id_grupo'] ? 'selected' : '' ?>><?= e($g['nombre']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Filtrar</button>
</form>

<table class="tabla">
  <thead>
    <tr><th>Código</th><th>Grupo</th><th>Promedio</th><th>Asistencia</th><th>Pendientes</th><th>Nivel de atención</th><th>Acciones</th></tr>
  </thead>
  <tbody>
  <?php if (!$estudiantes): ?>
    <tr><td colspan="7" class="vacio">No hay estudiantes que coincidan.</td></tr>
  <?php endif; ?>
  <?php foreach ($estudiantes as $est):
      $calc = calcular_nivel($est);
      $clase = strtolower(str_replace(' ', '-', $calc['nivel'])); ?>
    <tr>
      <td><strong><?= e($est['codigo']) ?></strong></td>
      <td><?= e($est['grupo']) ?></td>
      <td><?= e(number_format((float) $est['promedio_actual'], 2)) ?></td>
      <td><?= e(number_format((float) $est['asistencia_pct'], 1)) ?> %</td>
      <td><?= (int) $est['actividades_pendientes'] ?></td>
      <td><span class="nivel nivel-<?= e($clase) ?>"><?= e($calc['nivel']) ?></span></td>
      <td class="celda-acciones">
        <a href="<?= e(url('/estudiantes/ver.php')) ?>?id=<?= (int) $est['id_estudiante'] ?>">Ver</a>
        <a href="<?= e(url('/estudiantes/editar.php')) ?>?id=<?= (int) $est['id_estudiante'] ?>">Editar</a>
        <form method="post" action="<?= e(url('/estudiantes/eliminar.php')) ?>" class="en-linea"
              onsubmit="return confirm('¿Eliminar al estudiante <?= e($est['codigo']) ?> y sus alertas?');">
          <?= csrf_campo() ?>
          <input type="hidden" name="id" value="<?= (int) $est['id_estudiante'] ?>">
          <button type="submit" class="enlace-peligro">Eliminar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
