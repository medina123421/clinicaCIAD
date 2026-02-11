-- Tablas para Reportes de Laboratorio Completo

-- 1. Biometría Hemática
CREATE TABLE IF NOT EXISTS lab_biometria_hematica (
    id_biometria INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    
    -- Serie Roja
    eritrocitos DECIMAL(10,2), -- 10^6/uL
    hemoglobina DECIMAL(10,2), -- g/dL
    hematocrito DECIMAL(10,2), -- %
    vgm DECIMAL(10,2), -- fL
    hgm DECIMAL(10,2), -- pg
    cmhg DECIMAL(10,2), -- g/dL
    ide DECIMAL(10,2), -- % (RDW)
    
    -- Serie Blanca
    leucocitos DECIMAL(10,2), -- 10^3/uL
    neutrofilos_perc DECIMAL(10,2), -- %
    linfocitos_perc DECIMAL(10,2), -- %
    mid_perc DECIMAL(10,2), -- %
    neutrofilos_abs DECIMAL(10,2), -- 10^3/uL
    linfocitos_abs DECIMAL(10,2), -- 10^3/uL
    mid_abs DECIMAL(10,2), -- 10^3/uL
    
    -- Plaquetas
    plaquetas DECIMAL(10,2), -- 10^3/uL
    
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita)
);

-- 2. Química Sanguínea (6 Elementos + HbA1c si se desea aquí, pero la pondremos separate o aquí por comodidad del reporte)
-- La imagen 2 muestra "Química Sanguínea 6 Elementos" con Glucosa, Urea, BUN, Creatinina, Acido Urico, Colesterol, Trigliceridos (Son 7?? El titulo dice 6).
-- Vamos a incluir los 7 listados.
CREATE TABLE IF NOT EXISTS lab_quimica_sanguinea (
    id_quimica INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    
    glucosa DECIMAL(10,2), -- mg/dL
    urea DECIMAL(10,2), -- mg/dL
    bun DECIMAL(10,2), -- mg/dL
    creatinina DECIMAL(10,2), -- mg/dL
    acido_urico DECIMAL(10,2), -- mg/dL
    colesterol DECIMAL(10,2), -- mg/dL
    trigliceridos DECIMAL(10,2), -- mg/dL
    
    -- HbA1c aparece en otra hoja en las fotos, pero es química comúnmente. 
    -- La crearemos en tabla separada o aquí? El usuario quiere "un solo formulario".
    -- Si la hoja del lab las separa, mejor tenerla disponible.
    
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita)
);

-- 3. Examen General de Orina
CREATE TABLE IF NOT EXISTS lab_examen_orina (
    id_orina INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    
    -- Físico
    color VARCHAR(50),
    aspecto VARCHAR(50), -- No explicitly in list but standard
    
    -- Químico
    densidad VARCHAR(20), -- A veces es 1.015-1.025
    ph DECIMAL(4,1),
    leucocitos_quimico VARCHAR(50), -- Negativo/Traza/etc
    nitritos VARCHAR(50),
    proteinas VARCHAR(50),
    glucosa_quimico VARCHAR(50),
    sangre_quimico VARCHAR(50),
    cetonas VARCHAR(50),
    urobilinogeno VARCHAR(50),
    bilirrubina VARCHAR(50),
    
    -- Microscópico
    celulas_escamosas VARCHAR(50),
    celulas_cilindricas VARCHAR(50),
    celulas_urotelio VARCHAR(50),
    cristales VARCHAR(50), -- Escasas/etc
    celulas_renales VARCHAR(50),
    leucocitos_micro VARCHAR(50), -- 0-4/campo
    cilindros VARCHAR(50),
    eritrocitos_micro VARCHAR(50),
    dismorficos VARCHAR(50), -- 0%
    bacterias VARCHAR(50),
    hongos VARCHAR(50),
    levaduras VARCHAR(50),
    parasitos VARCHAR(50),
    
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita)
);
