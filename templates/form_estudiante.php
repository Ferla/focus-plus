<?php
/** Formulario compartido por crear.php y editar.php.
 *  Variables esperadas: $valores, $errores, $grupos, $accion, $boton */
function err(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? '<small class="error">' . e($errores[$campo]) . '</small>' : '';
}
?>
<form method="post" action="<?= e($accion) ?>" class="formulario" novalidate>
  <?= csrf_campo() ?>

  <label>Código del estudiante
    <input type="text" name="codigo" maxlength="20" value="<?= e($valores['codigo']) ?>" required>
    <?= err($errores, 'codigo') ?>
  </label>

  <label>Grupo
    <select name="id_grupo" required>
      <option value="">— Selecciona —</option>
      <?php foreach ($grupos as $g): ?>
        <option value="<?= (int) $g['id_grupo'] ?>" <?= (int) $valores['id_grupo'] === (int) $g['id_grupo'] ? 'selected' : '' ?>>
          <?= e($g['nombre']) ?> (<?= e($g['periodo']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <?= err($errores, 'id_grupo') ?>
  </label>

  <div class="fila">
    <label>Promedio actual (0–5)
      <input type="text" inputmode="decimal" name="promedio_actual" value="<?= e($valores['promedio_actual']) ?>" required>
      <?= err($errores, 'promedio_actual') ?>
    </label>
    <label>Promedio anterior (0–5)
      <input type="text" inputmode="decimal" name="promedio_anterior" value="<?= e($valores['promedio_anterior']) ?>" required>
      <?= err($errores, 'promedio_anterior') ?>
    </label>
  </div>

  <div class="fila">
    <label>Asistencia (%)
      <input type="text" inputmode="decimal" name="asistencia_pct" value="<?= e($valores['asistencia_pct']) ?>" required>
      <?= err($errores, 'asistencia_pct') ?>
    </label>
    <label>Actividades pendientes
      <input type="text" inputmode="numeric" name="actividades_pendientes" value="<?= e($valores['actividades_pendientes']) ?>" required>
      <?= err($errores, 'actividades_pendientes') ?>
    </label>
  </div>

  <div class="acciones">
    <button type="submit" class="btn btn-primario"><?= e($boton) ?></button>
    <a class="btn" href="<?= e(url('/estudiantes/')) ?>">Cancelar</a>
  </div>
</form>
