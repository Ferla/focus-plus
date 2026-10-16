# FOCUS+ · De la alerta a la intervención

Sistema web de **alertas tempranas y seguimiento académico** con decisiones humanas explicables.
Proyecto Cuatrimestral con IA · **Corte I** · Unisalamanca · SEP149 Nuevas Tecnologías.

> Reglas para detectar · IA para explicar · Personas para decidir.

## ¿Qué problema resuelve?
Las señales de riesgo académico (notas, asistencia, actividades, observaciones) están dispersas y el
seguimiento no siempre deja responsable, compromiso ni fecha de revisión. FOCUS+ reúne esas señales,
genera alertas **explicables** con reglas transparentes y guía al docente hasta registrar la
intervención y medir su resultado. El sistema **no predice "fracaso"** ni decide por nadie.

## Estado del proyecto (Corte I)
| Entregable | Estado |
|---|---|
| Problema, requisitos (RF/RNF) y modelo de datos | Hecho (ver PDF del Corte I y `database/schema.sql`) |
| Prototipo de pantallas | Hecho (PDF del prototipo) |
| **CRUD completo de la entidad `estudiantes`** conectado a MySQL | **Hecho** |
| Alertas con reglas, IA, intervenciones, seguimiento, login por roles | Cortes II y III |

## Tecnología
- **PHP 7.4+ (recomendado 8.1+)** (PDO, consultas preparadas) · **MySQL 8.0.16+** · HTML + CSS (sin frameworks)
- Servidor de desarrollo: `php -S` o XAMPP/Laragon

## Instalación y ejecución
1. Clona el repositorio:
   ```bash
   git clone https://github.com/<USUARIO>/focus-plus.git
   cd focus-plus
   ```
2. Crea la base de datos con datos sintéticos:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
3. Copia la configuración y ajusta usuario/contraseña de MySQL:
   ```bash
   cp config/config.example.php config/config.php
   ```
4. Inicia el servidor (la carpeta pública es `public/`):
   ```bash
   php -S localhost:8000 -t public
   ```
5. Abre <http://localhost:8000/estudiantes/>

### Con Laragon (Windows)
1. Copia la carpeta `focus-plus` dentro de `C:\laragon\www\`.
2. Abre Laragon y pulsa **Iniciar todo** (Apache y MySQL en verde).
3. Crea la base: en Laragon, clic en **Base de datos** (HeidiSQL) → *Archivo → Cargar archivo SQL* → `database\schema.sql` → ejecutar (F9).
4. Copia `config\config.example.php` como `config\config.php` (Laragon usa usuario `root` y contraseña vacía).
5. Abre <http://localhost/focus-plus/> (o <http://focus-plus.test/> si tienes *Pretty URLs* activado). Funciona aunque la raíz sea la carpeta del proyecto o `public/`.

Si ves una página en blanco, activa *Menú → PHP → Version* con PHP 7.4 o superior y revisa `C:\laragon\bin\apache\...\logs\error.log`.

## CRUD de `estudiantes`
| Operación | Archivo | Método |
|---|---|---|
| Listar / buscar / filtrar | `public/estudiantes/index.php` | GET |
| Ver detalle + alerta explicable | `public/estudiantes/ver.php?id=` | GET |
| Crear | `public/estudiantes/crear.php` | GET / POST |
| Editar | `public/estudiantes/editar.php?id=` | GET / POST |
| Eliminar | `public/estudiantes/eliminar.php` | POST |

Validaciones: código único (3–20 caracteres), grupo existente, promedios 0–5, asistencia 0–100 %,
pendientes 0–100. La lista muestra el **nivel de atención** calculado con las reglas del SRS
(descenso de promedio ≤ −0,5 · asistencia < 80 % · ≥ 3 pendientes).

## Estructura
```
focus-plus/
├── config/            config.example.php (config.php local, ignorado por git)
├── database/          schema.sql (6 tablas + datos sintéticos)
├── src/               db.php (conexión PDO) · helpers.php (CSRF, validación, reglas)
├── templates/         header, footer y formulario compartido
└── public/            raíz web: index.php, estudiantes/*.php, assets/style.css
```

## Seguridad aplicada
Consultas preparadas (anti inyección SQL) · escape de salida con `htmlspecialchars` (anti XSS) ·
token CSRF en todos los POST · eliminación solo por POST · credenciales fuera del repositorio ·
**solo datos sintéticos**, estudiantes identificados por código (sin nombres ni documentos).

## Uso de IA
Usamos IA como asistente y lo registramos en la **Bitácora de uso de IA** entregada con este corte.
Cada integrante debe poder explicar el código de este repositorio.

## Equipo y contribuciones
| Integrante | Aporte en el Corte I |
|---|---|
| Jhon Ferla | _completar_ |
| Henry Estrada | _completar_ |
| Ricardo Zuñiga | _completar_ |
| Duván Aponte | _completar_ |
| Javier Tovar | _completar_ |

## Próximos pasos
- **Corte II:** login por roles, motor de reglas con alertas guardadas, validación docente, intervenciones.
- **Corte III:** seguimiento, comparación antes/después, resumen y sugerencias con IA, indicadores.
