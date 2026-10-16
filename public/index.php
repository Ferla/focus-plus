<?php
// Inicio: lleva al CRUD de estudiantes sin importar en qué carpeta esté el proyecto.
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header('Location: ' . $base . '/estudiantes/');
exit;
