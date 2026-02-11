-- Nuevas tablas para Perfil Hepático, Perfil Tiroideo e Insulina Basal

-- 1. Perfil Hepático
CREATE TABLE IF NOT EXISTS lab_perfil_hepatico (
    id_hepatico INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    
    bilirrubina_total DECIMAL(10,2),
    bilirrubina_directa DECIMAL(10,2),
    bilirrubina_indirecta DECIMAL(10,2),
    alt_gpt DECIMAL(10,2),
    ast_got DECIMAL(10,2),
    fosfatasa_alcalina DECIMAL(10,2),
    ggt DECIMAL(10,2),
    proteinas_totales DECIMAL(10,2),
    albumina DECIMAL(10,2),
    globulina DECIMAL(10,2),
    
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
);

-- 2. Perfil Tiroideo
CREATE TABLE IF NOT EXISTS lab_perfil_tiroideo (
    id_tiroideo INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    
    t3_total DECIMAL(10,2),
    t3_libre DECIMAL(10,2),
    t4_total DECIMAL(10,2),
    t4_libre DECIMAL(10,2),
    tsh DECIMAL(10,2),
    
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
);

-- 3. Insulina Basal
CREATE TABLE IF NOT EXISTS lab_insulina (
    id_insulina INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    
    insulina_basal DECIMAL(10,2),
    
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
);
