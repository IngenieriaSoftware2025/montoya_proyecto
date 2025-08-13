CREATE DATABASE monitoreo WITH LOG;

-- ==============================
-- ROLES Y USUARIOS
-- ==============================

--AUN NO
-- -- Tabla: rol
-- CREATE TABLE rol (
--     rol_id      SERIAL PRIMARY KEY,
--     rol_nombre  VARCHAR(30) NOT NULL,
--     rol_situacion SMALLINT DEFAULT 1
-- );

-- Tabla: usuario
CREATE TABLE usuario (
    usu_id      SERIAL PRIMARY KEY,
    usu_nombre  VARCHAR(120) NOT NULL,
    usu_grado   VARCHAR(50),
    usu_email   VARCHAR(120),
    usu_rol_id  INT NOT NULL,
    usu_activo  BOOLEAN,
    usu_situacion SMALLINT DEFAULT 1
    -- aun no FOREIGN KEY (usu_rol_id) REFERENCES rol(rol_id)
);

-- ==============================
-- APLICACIONES / PROYECTOS
-- ==============================

-- Tabla: aplicacion
CREATE TABLE aplicacion (
    apl_id           SERIAL PRIMARY KEY,
    apl_nombre       VARCHAR(120) NOT NULL,
    apl_descripcion  LVARCHAR(500),
    apl_fecha_inicio DATE NOT NULL,
    apl_fecha_fin    DATE,
    apl_porcentaje_objetivo SMALLINT,
    apl_estado       VARCHAR(20),   -- EN_PLANIFICACION, EN_PROGRESO, PAUSADO, CERRADO
    apl_responsable  INT,           -- usuario (desarrollador principal)
    apl_creado_en    DATETIME YEAR TO SECOND,
    apl_situacion    SMALLINT DEFAULT 1,
    FOREIGN KEY (apl_responsable) REFERENCES usuario(usu_id)
);

-- ==============================
-- AVANCE DIARIO (DESARROLLADOR)
-- ==============================
SELECT ava_fecha, ava_apl_id, ava_usu_id, apl_nombre 
FROM avance_diario av
INNER JOIN aplicacion ap ON av.ava_apl_id = ap.apl_id
WHERE av.ava_usu_id = 3 
AND av.ava_situacion = 1
ORDER BY ava_fecha DESC;

select * from avance_diario
-- Tabla: avance_diario
CREATE TABLE avance_diario (
    ava_id           SERIAL PRIMARY KEY,
    ava_apl_id       INT NOT NULL,
    ava_usu_id       INT NOT NULL,
    ava_fecha        DATE NOT NULL,
    ava_porcentaje   SMALLINT NOT NULL,
    ava_resumen      LVARCHAR(800),
    ava_bloqueadores LVARCHAR(400),
    ava_justificacion LVARCHAR(800), -- si el % baja
    ava_creado_en    DATETIME YEAR TO SECOND,
    ava_situacion    SMALLINT DEFAULT 1,
    FOREIGN KEY (ava_apl_id) REFERENCES aplicacion(apl_id),
    FOREIGN KEY (ava_usu_id) REFERENCES usuario(usu_id),
    UNIQUE (ava_apl_id, ava_usu_id, ava_fecha)
);

-- ==============================
-- INACTIVIDAD / NO AVANCE
-- ==============================

-- Tabla: inactividad_diaria
CREATE TABLE inactividad_diaria (
    ina_id        SERIAL PRIMARY KEY,
    ina_apl_id    INT NOT NULL,
    ina_usu_id    INT NOT NULL,
    ina_fecha     DATE NOT NULL,
    ina_motivo    LVARCHAR(500) NOT NULL,
    ina_tipo      VARCHAR(50), -- LICENCIA, FALLA_TECNICA, BLOQUEADOR_EXTERNO, VISITA, ESPERA_APROBACION
    ina_creado_en DATETIME YEAR TO SECOND,
    ina_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (ina_apl_id) REFERENCES aplicacion(apl_id),
    FOREIGN KEY (ina_usu_id) REFERENCES usuario(usu_id),
    UNIQUE (ina_apl_id, ina_usu_id, ina_fecha)
);

-- ==============================
-- VISITAS / FEEDBACK
-- ==============================

-- Tabla: visita
CREATE TABLE visita (
    vis_id            SERIAL PRIMARY KEY,
    vis_apl_id        INT NOT NULL,
    vis_fecha         DATETIME YEAR TO MINUTE NOT NULL,
    vis_quien         VARCHAR(150),
    vis_motivo        LVARCHAR(400),
    vis_procedimiento LVARCHAR(400),
    vis_solucion      LVARCHAR(400),
    vis_observacion   LVARCHAR(800),
    vis_conformidad   BOOLEAN,
    vis_creado_por    INT,
    vis_creado_en     DATETIME YEAR TO SECOND,
    vis_situacion     SMALLINT DEFAULT 1,
    FOREIGN KEY (vis_apl_id) REFERENCES aplicacion(apl_id),
    FOREIGN KEY (vis_creado_por) REFERENCES usuario(usu_id)
);

-- ==============================
-- COMENTARIOS + LECTURAS
-- ==============================

-- Tabla: comentario
CREATE TABLE comentario (
    com_id        SERIAL PRIMARY KEY,
    com_apl_id    INT NOT NULL,
    com_autor_id  INT NOT NULL,
    com_texto     LVARCHAR(1200) NOT NULL,
    com_creado_en DATETIME YEAR TO SECOND,
    com_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (com_apl_id) REFERENCES aplicacion(apl_id),
    FOREIGN KEY (com_autor_id) REFERENCES usuario(usu_id)
);

-- Tabla: comentario_leido
CREATE TABLE comentario_leido (
    col_id       SERIAL PRIMARY KEY,
    col_com_id   INT NOT NULL,
    col_usu_id   INT NOT NULL,
    col_leido_en DATETIME YEAR TO SECOND,
    col_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (col_com_id) REFERENCES comentario(com_id),
    FOREIGN KEY (col_usu_id) REFERENCES usuario(usu_id),
    UNIQUE (col_com_id, col_usu_id)
);
