<?php
/** Cabecera común. Antes de incluirla define $titulo. */
$flash = flash_leer();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo ?? 'FOCUS+') ?> · FOCUS+</title>
  <link rel="stylesheet" href="<?= e(url('/assets/style.css')) ?>">
</head>
<body>
<header class="barra">
  <div class="marca">FOCUS<span>+</span></div>
  <nav><a href="<?= e(url('/estudiantes/')) ?>">Estudiantes</a></nav>
</header>
<main class="contenedor">
<?php if ($flash): ?>
  <div class="aviso aviso-<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div>
<?php endif; ?>
