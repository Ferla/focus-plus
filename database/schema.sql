-- FOCUS+ · Corte I · Esquema MySQL 8.0.16+ (InnoDB, utf8mb4)
-- Importar: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS focus_plus
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE focus_plus;

DROP TABLE IF EXISTS seguimientos;
DROP TABLE IF EXISTS intervenciones;
DROP TABLE IF EXISTS alertas;
DROP TABLE IF EXISTS estudiantes;
DROP TABLE IF EXISTS grupos;
DROP TABLE IF EXISTS usuarios;

-- 1) Usuarios del sistema (el login se implementa en el Corte II)
CREATE TABLE usuarios (
  id_usuario      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  correo          VARCHAR(120) NOT NULL,
  hash_contrasena VARCHAR(255) NOT NULL,
  rol             ENUM('docente','coordinacion','estudiante','institucion','administrador') NOT NULL,
  activo          TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuarios_correo (correo)
) ENGINE=InnoDB;

-- 2) Grupos académicos, cada uno a cargo de un docente
CREATE TABLE grupos (
  id_grupo   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre     VARCHAR(60) NOT NULL,
  periodo    VARCHAR(10) NOT NULL,
  id_docente INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_grupo),
  CONSTRAINT fk_grupos_docente FOREIGN KEY (id_docente) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB;

-- 3) Estudiantes seudonimizados (solo código, sin nombre ni documento)  <-- ENTIDAD DEL CRUD
CREATE TABLE estudiantes (
  id_estudiante          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo                 VARCHAR(20) NOT NULL,
  id_grupo               INT UNSIGNED NOT NULL,
  promedio_actual        DECIMAL(3,2) NOT NULL,
  promedio_anterior      DECIMAL(3,2) NOT NULL,
  asistencia_pct         DECIMAL(5,2) NOT NULL,
  actividades_pendientes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  creado_en              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_estudiante),
  UNIQUE KEY uq_estudiantes_codigo (codigo),
  CONSTRAINT fk_estudiantes_grupo FOREIGN KEY (id_grupo) REFERENCES grupos (id_grupo),
  CONSTRAINT ck_prom_actual   CHECK (promedio_actual   BETWEEN 0 AND 5),
  CONSTRAINT ck_prom_anterior CHECK (promedio_anterior BETWEEN 0 AND 5),
  CONSTRAINT ck_asistencia    CHECK (asistencia_pct    BETWEEN 0 AND 100)
) ENGINE=InnoDB;

-- 4) Alertas generadas por las reglas (Corte II)
CREATE TABLE alertas (
  id_alerta         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_estudiante     INT UNSIGNED NOT NULL,
  nivel             ENUM('alto','medio','bajo') NOT NULL,
  estado_validacion ENUM('pendiente','confirmada','ajustada','descartada') NOT NULL DEFAULT 'pendiente',
  explicacion       TEXT NULL,
  creada_en         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_alerta),
  CONSTRAINT fk_alertas_estudiante FOREIGN KEY (id_estudiante)
    REFERENCES estudiantes (id_estudiante) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5) Intervenciones de acompañamiento (Corte II)
CREATE TABLE intervenciones (
  id_intervencion INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_alerta       INT UNSIGNED NOT NULL,
  tipo            VARCHAR(40) NOT NULL,
  id_responsable  INT UNSIGNED NOT NULL,
  compromiso      VARCHAR(255) NOT NULL,
  fecha_revision  DATE NOT NULL,
  creada_en       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_intervencion),
  CONSTRAINT fk_interv_alerta FOREIGN KEY (id_alerta) REFERENCES alertas (id_alerta) ON DELETE CASCADE,
  CONSTRAINT fk_interv_responsable FOREIGN KEY (id_responsable) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB;

-- 6) Seguimientos y resultado (Corte III)
CREATE TABLE seguimientos (
  id_seguimiento  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_intervencion INT UNSIGNED NOT NULL,
  estado          ENUM('pendiente','en_curso','cumplida','no_cumplida') NOT NULL DEFAULT 'pendiente',
  resultado       TEXT NULL,
  fecha           DATE NOT NULL,
  PRIMARY KEY (id_seguimiento),
  CONSTRAINT fk_seg_interv FOREIGN KEY (id_intervencion)
    REFERENCES intervenciones (id_intervencion) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Datos sintéticos (ficticios) ----------
-- La semilla NO sirve para iniciar sesión (el login llega en el Corte II).
INSERT INTO usuarios (correo, hash_contrasena, rol) VALUES
  ('docente.demo@focus.test', 'sin_login_en_corte_1', 'docente');

INSERT INTO grupos (nombre, periodo, id_docente) VALUES
  ('Grupo A - Matemáticas', '2026-2', 1),
  ('Grupo B - Lenguaje',    '2026-2', 1);

INSERT INTO estudiantes (codigo, id_grupo, promedio_actual, promedio_anterior, asistencia_pct, actividades_pendientes) VALUES
  ('EST-001', 1, 3.20, 3.80, 72.00, 3),
  ('EST-002', 1, 4.10, 4.00, 96.00, 0),
  ('EST-003', 1, 2.80, 3.50, 85.00, 1),
  ('EST-004', 1, 3.50, 3.60, 76.00, 4),
  ('EST-005', 1, 4.50, 4.40, 98.00, 0),
  ('EST-006', 2, 3.00, 3.10, 79.00, 2),
  ('EST-007', 2, 3.90, 3.90, 90.00, 1),
  ('EST-008', 2, 2.50, 3.20, 70.00, 5),
  ('EST-009', 2, 4.20, 4.30, 94.00, 0),
  ('EST-010', 2, 3.40, 3.50, 88.00, 3);
-- EST-001 es el caso demo: descenso + inasistencia + pendientes = nivel Alto.
