-- Esquema
CREATE TABLE IF NOT EXISTS attractions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    image_url VARCHAR(255) DEFAULT NULL,
    maintenance TINYINT(1) NOT NULL DEFAULT 0,
    duration_minutes INT DEFAULT NULL,
    min_height_cm INT DEFAULT NULL,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    label VARCHAR(100) NOT NULL,
    price DECIMAL(8, 2) NOT NULL,
    description VARCHAR(255)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_email VARCHAR(150) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    ticket_type_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(8, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_type_id) REFERENCES ticket_types (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Inserció de tipus de ticket
INSERT INTO
    ticket_types (
        code,
        label,
        price,
        description
    )
VALUES (
        'ADULT',
        'Entrada Adult',
        45.00,
        'Entrada general per a adults (12+ anys)'
    ),
    (
        'CHILD',
        'Entrada Infantil',
        25.00,
        'Entrada per a nens (3-11 anys)'
    ),
    (
        'VIP',
        'Entrada VIP',
        75.00,
        'Accés prioritari + àrea VIP exclusiva'
    ),
    (
        'FAMILY',
        'Pack Familiar',
        120.00,
        '2 adults + 2 nens (estalvi familiar)'
    );

-- Inserció d'atraccions del parc de cotxes esportius
INSERT INTO
    attractions (
        name,
        description,
        maintenance,
        duration_minutes,
        min_height_cm,
        category
    )
VALUES (
        'Fórmula Ràpid',
        'Rèplica de cotxe de F1 amb acceleració extrema',
        0,
        3,
        140,
        'Extrema'
    ),
    (
        'Derrapades Esportives',
        'Circuit de derrapades amb cotxes esportius',
        0,
        5,
        120,
        'Habilitat'
    ),
    (
        'Pista Supercars',
        'Conducte els millors supercars del mercat',
        1,
        8,
        150,
        'Experiència'
    ),
    (
        'Muntanya Russa Racing',
        'Muntanya russa temàtica de curses',
        0,
        2,
        130,
        'Extrema'
    ),
    (
        'Simulador F1 Professional',
        'Simulador realista de F1 amb moviment',
        0,
        10,
        110,
        'Simulació'
    ),
    (
        'Pit Stop Challenge',
        'Canvi de rodes ràpid com a equip de F1',
        0,
        4,
        100,
        'Habilitat'
    ),
    (
        'Circuito Clàssics',
        'Exposició i conducció de cotxes clàssics',
        0,
        15,
        0,
        'Exposició'
    ),
    (
        'Drag Racing',
        'Acceleració màxima en recta de 400m',
        0,
        2,
        140,
        'Extrema'
    ),
    (
        'Taller de Tuning',
        'Personalitza el teu cotxe virtual',
        1,
        20,
        0,
        'Creativitat'
    ),
    (
        'Karting Professional',
        'Karts de competició en circuit tècnic',
        0,
        6,
        120,
        'Competició'
    ),
    (
        'Museu del Motor',
        'Col·lecció de cotxes històrics esportius',
        0,
        30,
        0,
        'Cultural'
    ),
    (
        'Revolucions Motors',
        'Gir ràpid en plataforma centrifugadora',
        0,
        3,
        130,
        'Extrema'
    );
