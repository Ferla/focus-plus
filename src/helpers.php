<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escapa texto antes de mostrarlo en HTML (protege contra XSS). */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Redirige a otra página y termina el script. */
function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------- URLs: funcionan en Laragon, XAMPP o php -S, estén donde estén ---------- */
function base_url(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($script, '/estudiantes/');
    if ($pos !== false) {
        return substr($script, 0, $pos);
    }
    return rtrim(dirname($script), '/');
}

function url(string $ruta): string
{
    return base_url() . $ruta;
}

/* ---------- Mensajes temporales (flash) ---------- */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function flash_leer(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Protección CSRF para formularios POST ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals($_SESSION['csrf'] ?? '', $enviado)) {
        http_response_code(419);
        exit('Formulario caducado o inválido. Vuelve atrás y recarga la página.');
    }
}

/* ---------- Regla de negocio (vista previa del motor de alertas) ---------- */
/**
 * Aplica las reglas iniciales del SRS (RF-05) a un estudiante:
 *  - Descenso de promedio <= -0,5
 *  - Asistencia < 80 %
 *  - 3 o más actividades pendientes
 * Nivel: 3 factores = Alto, 2 = Medio, 1 = Bajo, 0 = Sin alerta.
 */
function calcular_nivel(array $est): array
{
    $factores = [];
    $variacion = (float) $est['promedio_actual'] - (float) $est['promedio_anterior'];
    if ($variacion <= -0.5) {
        $factores[] = 'Descenso de promedio (' . number_format($variacion, 2) . ')';
    }
    if ((float) $est['asistencia_pct'] < 80) {
        $factores[] = 'Asistencia baja (' . number_format((float) $est['asistencia_pct'], 1) . ' %)';
    }
    if ((int) $est['actividades_pendientes'] >= 3) {
        $factores[] = 'Actividades pendientes (' . (int) $est['actividades_pendientes'] . ')';
    }
    $niveles = [0 => 'Sin alerta', 1 => 'Bajo', 2 => 'Medio', 3 => 'Alto'];
    return ['nivel' => $niveles[count($factores)], 'factores' => $factores];
}

/* ---------- Validación del formulario de estudiante ---------- */
/**
 * Devuelve [errores, datos_limpios]. $idActual sirve para permitir el mismo
 * código al editar el mismo registro.
 */
function validar_estudiante(array $in, PDO $pdo, ?int $idActual = null): array
{
    $errores = [];

    $codigo = strtoupper(trim((string) ($in['codigo'] ?? '')));
    if (!preg_match('/^[A-Z0-9-]{3,20}$/', $codigo)) {
        $errores['codigo'] = 'El código debe tener 3 a 20 caracteres (letras, números o guion).';
    } else {
        $sql = 'SELECT COUNT(*) FROM estudiantes WHERE codigo = ?';
        $par = [$codigo];
        if ($idActual !== null) {
            $sql .= ' AND id_estudiante <> ?';
            $par[] = $idActual;
        }
        $st = $pdo->prepare($sql);
        $st->execute($par);
        if ((int) $st->fetchColumn() > 0) {
            $errores['codigo'] = 'Ya existe un estudiante con ese código.';
        }
    }

    $idGrupo = filter_var($in['id_grupo'] ?? '', FILTER_VALIDATE_INT);
    if ($idGrupo === false) {
        $errores['id_grupo'] = 'Selecciona un grupo.';
    } else {
        $st = $pdo->prepare('SELECT COUNT(*) FROM grupos WHERE id_grupo = ?');
        $st->execute([$idGrupo]);
        if ((int) $st->fetchColumn() === 0) {
            $errores['id_grupo'] = 'El grupo seleccionado no existe.';
        }
    }

    $actual   = str_replace(',', '.', trim((string) ($in['promedio_actual'] ?? '')));
    $anterior = str_replace(',', '.', trim((string) ($in['promedio_anterior'] ?? '')));
    if (!is_numeric($actual) || (float) $actual < 0 || (float) $actual > 5) {
        $errores['promedio_actual'] = 'El promedio actual debe estar entre 0,0 y 5,0.';
    }
    if (!is_numeric($anterior) || (float) $anterior < 0 || (float) $anterior > 5) {
        $errores['promedio_anterior'] = 'El promedio anterior debe estar entre 0,0 y 5,0.';
    }

    $asis = str_replace(',', '.', trim((string) ($in['asistencia_pct'] ?? '')));
    if (!is_numeric($asis) || (float) $asis < 0 || (float) $asis > 100) {
        $errores['asistencia_pct'] = 'La asistencia debe estar entre 0 y 100 %.';
    }

    $pend = filter_var($in['actividades_pendientes'] ?? '', FILTER_VALIDATE_INT);
    if ($pend === false || $pend < 0 || $pend > 100) {
        $errores['actividades_pendientes'] = 'Las actividades pendientes deben ser un entero entre 0 y 100.';
    }

    $datos = [
        'codigo'                 => $codigo,
        'id_grupo'               => $idGrupo === false ? 0 : $idGrupo,
        'promedio_actual'        => is_numeric($actual) ? round((float) $actual, 2) : 0,
        'promedio_anterior'      => is_numeric($anterior) ? round((float) $anterior, 2) : 0,
        'asistencia_pct'         => is_numeric($asis) ? round((float) $asis, 2) : 0,
        'actividades_pendientes' => $pend === false ? 0 : $pend,
    ];
    return [$errores, $datos];
}
