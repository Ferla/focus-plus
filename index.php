<?php
// Si Laragon/XAMPP apunta a la carpeta del proyecto (y no a public/), este archivo redirige.
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header('Location: ' . $base . '/public/estudiantes/');
exit;
