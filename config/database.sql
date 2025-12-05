-- Création de la base de données
CREATE DATABASE IF NOT EXISTS hec_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hec_db;

-- Table des rôles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion des rôles par défaut
INSERT INTO roles (nom, description) VALUES 
('admin', 'Administrateur système (Dr Kamagaté)'),
('chef_classe', 'Chef de classe'),
('professeur', 'Enseignant'),
('etudiant', 'Étudiant');

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    first_login BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des années universitaires
CREATE TABLE IF NOT EXISTS annees_universitaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    annee_debut YEAR NOT NULL,
    annee_fin YEAR NOT NULL,
    est_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_annee (annee_debut, annee_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des semestres
CREATE TABLE IF NOT EXISTS semestres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    annee_universitaire_id INT NOT NULL,
    numero INT NOT NULL,
    date_debut DATE,
    date_fin DATE,
    est_ouvert BOOLEAN DEFAULT FALSE,
    est_cloture BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (annee_universitaire_id) REFERENCES annees_universitaires(id),
    UNIQUE KEY unique_semestre (annee_universitaire_id, numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des classes
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    intitule VARCHAR(100) NOT NULL,
    niveau VARCHAR(20),
    annee_universitaire_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (annee_universitaire_id) REFERENCES annees_universitaires(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des matières (UE)
CREATE TABLE IF NOT EXISTS matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    intitule VARCHAR(100) NOT NULL,
    credits INT,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des étudiants
CREATE TABLE IF NOT EXISTS etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    matricule VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_naissance DATE,
    lieu_naissance VARCHAR(100),
    telephone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des professeurs
CREATE TABLE IF NOT EXISTS professeurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table d'affectation des professeurs aux matières
CREATE TABLE IF NOT EXISTS affectation_professeur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professeur_id INT NOT NULL,
    matiere_id INT NOT NULL,
    classe_id INT NOT NULL,
    annee_universitaire_id INT NOT NULL,
    FOREIGN KEY (professeur_id) REFERENCES professeurs(id),
    FOREIGN KEY (matiere_id) REFERENCES matieres(id),
    FOREIGN KEY (classe_id) REFERENCES classes(id),
    FOREIGN KEY (annee_universitaire_id) REFERENCES annees_universitaires(id),
    UNIQUE KEY unique_affectation (professeur_id, matiere_id, classe_id, annee_universitaire_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des inscriptions des étudiants
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    classe_id INT NOT NULL,
    annee_universitaire_id INT NOT NULL,
    date_inscription DATE DEFAULT CURRENT_DATE,
    statut VARCHAR(20) DEFAULT 'actif',
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id),
    FOREIGN KEY (classe_id) REFERENCES classes(id),
    FOREIGN KEY (annee_universitaire_id) REFERENCES annees_universitaires(id),
    UNIQUE KEY unique_inscription (etudiant_id, annee_universitaire_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des chefs de classe
CREATE TABLE IF NOT EXISTS chef_classe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    classe_id INT NOT NULL,
    annee_universitaire_id INT NOT NULL,
    date_nomination DATE DEFAULT CURRENT_DATE,
    est_actif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id),
    FOREIGN KEY (classe_id) REFERENCES classes(id),
    FOREIGN KEY (annee_universitaire_id) REFERENCES annees_universitaires(id),
    UNIQUE KEY unique_chef_classe (classe_id, annee_universitaire_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des notes
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    matiere_id INT NOT NULL,
    classe_id INT NOT NULL,
    semestre_id INT NOT NULL,
    session VARCHAR(20) NOT NULL,
    note DECIMAL(4,2) NOT NULL,
    appreciation TEXT,
    statut VARCHAR(20) DEFAULT 'brouillon', -- brouillon, soumis, valide, publie
    saisie_par INT NOT NULL, -- user_id de la personne qui a saisi la note
    valide_par INT, -- user_id de l'admin qui a validé
    date_saisie TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_validation TIMESTAMP NULL,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id),
    FOREIGN KEY (matiere_id) REFERENCES matieres(id),
    FOREIGN KEY (classe_id) REFERENCES classes(id),
    FOREIGN KEY (semestre_id) REFERENCES semestres(id),
    FOREIGN KEY (saisie_par) REFERENCES users(id),
    FOREIGN KEY (valide_par) REFERENCES users(id),
    UNIQUE KEY unique_note (etudiant_id, matiere_id, semestre_id, session)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table d'historique des modifications de notes
CREATE TABLE IF NOT EXISTS historique_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    ancienne_valeur DECIMAL(4,2),
    nouvelle_valeur DECIMAL(4,2),
    modifie_par INT NOT NULL,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motif TEXT,
    FOREIGN KEY (note_id) REFERENCES notes(id),
    FOREIGN KEY (modifie_par) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de l'utilisateur admin par défaut
-- Mot de passe: admin123 (à changer après la première connexion)
INSERT INTO users (username, email, password, role_id, first_login, is_active) 
VALUES ('admin', 'admin@hec.ci', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 1);

-- Insertion d'une année universitaire exemple
INSERT INTO annees_universitaires (annee_debut, annee_fin, est_active) 
VALUES (2025, 2026, 1);

-- Insertion des semestres pour l'année 2025-2026
INSERT INTO semestres (annee_universitaire_id, numero, date_debut, date_fin, est_ouvert, est_cloture)
VALUES 
(1, 1, '2025-09-01', '2025-12-31', 1, 0),
(1, 2, '2026-01-01', '2026-05-31', 0, 0);

-- Insertion de quelques matières exemple
INSERT INTO matieres (code, intitule, credits) VALUES 
('MATH1', 'Mathématiques 1', 4),
('PHYS1', 'Physique 1', 3),
('INFO1', 'Informatique 1', 4),
('ECO1', 'Économie 1', 3),
('GEST1', 'Gestion 1', 3);
