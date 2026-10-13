CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    school_name VARCHAR(255) NOT NULL,
    interested_trade ENUM(
        'Informatika',
        'Gépészet',
        'Kereskedelem',
        'Turizmus-vendéglátás',
        'Egyéb'
    ) NOT NULL,
    accommodation BOOLEAN NOT NULL DEFAULT FALSE,
    is_graduating BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);