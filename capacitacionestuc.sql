-- ===========================================================
-- BASE DE DATOS COMPLETA PARA SISTEMA TUC CAPACITACIONES
-- ===========================================================

CREATE DATABASE IF NOT EXISTS capacitaciones_tuc
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE capacitaciones_tuc;

-- ===========================================================
-- TABLA DE ALUMNOS
-- ===========================================================

CREATE TABLE IF NOT EXISTS alumnos_tuc (
    id_alumno INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(30),
    email VARCHAR(150),
    estado ENUM('A','I') DEFAULT 'A'
);

-- Índices opcionales
CREATE INDEX idx_alumno_nombre ON alumnos_tuc(nombre);
CREATE INDEX idx_alumno_apellido ON alumnos_tuc(apellido);


-- ===========================================================
-- TABLA DE CURSOS
-- ===========================================================

CREATE TABLE IF NOT EXISTS cursos_tuc (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    nombre_curso VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    estado ENUM('A','I') DEFAULT 'A'
);

CREATE INDEX idx_nombre_curso ON cursos_tuc(nombre_curso);


-- ===========================================================
-- TABLA DE INSCRIPCIONES
-- ===========================================================

CREATE TABLE IF NOT EXISTS inscripciones_tuc (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno INT NOT NULL,
    id_curso INT NOT NULL,
    fecha_inscripcion DATE DEFAULT CURRENT_DATE,
    UNIQUE (id_alumno, id_curso),
    FOREIGN KEY (id_alumno) REFERENCES alumnos_tuc(id_alumno) ON DELETE CASCADE,
    FOREIGN KEY (id_curso) REFERENCES cursos_tuc(id_curso) ON DELETE CASCADE
);

CREATE INDEX idx_insc_alumno ON inscripciones_tuc(id_alumno);
CREATE INDEX idx_insc_curso  ON inscripciones_tuc(id_curso);


-- ===========================================================
-- TABLA DE PAGOS
-- ===========================================================

CREATE TABLE IF NOT EXISTS pagos_tuc (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno INT NOT NULL,
    id_curso INT NOT NULL,
    mes_pagado INT NOT NULL,
    fecha_pago DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (id_alumno) REFERENCES alumnos_tuc(id_alumno) ON DELETE CASCADE,
    FOREIGN KEY (id_curso) REFERENCES cursos_tuc(id_curso) ON DELETE CASCADE,
    UNIQUE (id_alumno, id_curso, mes_pagado)
);

CREATE INDEX idx_pago_alumno ON pagos_tuc(id_alumno);
CREATE INDEX idx_pago_curso  ON pagos_tuc(id_curso);


-- ===========================================================
-- TABLA DE LLAMADAS
-- ===========================================================

CREATE TABLE IF NOT EXISTS llamadas_tuc (
    id_llamada INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno INT NOT NULL,
    fecha_llamada DATE DEFAULT CURRENT_DATE,
    comentario TEXT,
    FOREIGN KEY (id_alumno) REFERENCES alumnos_tuc(id_alumno) ON DELETE CASCADE
);

CREATE INDEX idx_llamada_alumno ON llamadas_tuc(id_alumno);



