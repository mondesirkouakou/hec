<?php
/**
 * Classe pour la gestion des chefs de classe
 * Étend la classe User pour ajouter des fonctionnalités spécifiques aux chefs de classe
 */
class ChefClasse extends User {
    // Propriétés spécifiques aux chefs de classe
    protected $classe_id;
    protected $date_nomination;
    protected $classe_nom;
    protected $classe_niveau;
    
    // Tables de la base de données
    protected $table_chef_classe = 'chef_classe';
    protected $table_etudiants = 'etudiants';
    protected $table_professeurs = 'professeurs';
    protected $table_classes = 'classes';
    protected $table_affectation = 'affectation_professeur';
    protected $table_inscriptions = 'inscriptions';

    /**
     * Constructeur
     * @param int $userId ID de l'utilisateur chef de classe
     */
    public function __construct($userId = null) {
        parent::__construct();
        
        if ($userId) {
            $this->loadChefClasseData($userId);
        }
    }
    
    /**
     * Charge les données du chef de classe
     * @param int $userId ID de l'utilisateur
     */
    protected function loadChefClasseData($userId) {
        $sql = "SELECT cc.*, c.intitule as classe_nom, c.niveau as classe_niveau
                FROM {$this->table_chef_classe} cc
                JOIN {$this->table_classes} c ON cc.classe_id = c.id
                JOIN {$this->table_etudiants} e ON cc.etudiant_id = e.id
                WHERE e.user_id = :user_id";
        
        $data = $this->db->fetch($sql, ['user_id' => $userId]);
        
        if ($data) {
            $this->classe_id = $data['classe_id'];
            $this->date_nomination = $data['date_nomination'];
            $this->classe_nom = $data['classe_nom'];
            $this->classe_niveau = $data['classe_niveau'];
        }
    }

    /**
     * Vérifie si l'utilisateur est chef de classe
     * @param int $userId ID de l'utilisateur
     * @return bool True si l'utilisateur est chef de classe, false sinon
     */
    public function estChefClasse($userId) {
        $sql = "SELECT COUNT(*) 
                FROM {$this->table_chef_classe} cc
                JOIN {$this->table_etudiants} e ON cc.etudiant_id = e.id
                WHERE e.user_id = :user_id";
        
        return $this->db->fetchColumn($sql, ['user_id' => $userId]) > 0;
    }
    
    /**
     * Récupère les informations de la classe gérée
     * @return array|false Les données de la classe ou false si non trouvée
     */
    public function getClasse() {
        if (!$this->classe_id) {
            return false;
        }
        
        return $this->db->fetch(
            "SELECT * FROM {$this->table_classes} WHERE id = :id",
            ['id' => $this->classe_id]
        );
    }
    
    /**
     * Récupère la liste des étudiants de la classe
     * @param bool $avecDetails Si vrai, inclut les détails des utilisateurs
     * @return array Liste des étudiants
     */
    public function getListeEtudiants($avecDetails = true) {
        if (!$this->classe_id) {
            return [];
        }
        
        if ($avecDetails) {
            $sql = "SELECT e.*, u.email, u.is_active, u.first_login,
                           CONCAT(e.nom, ' ', e.prenom) as nom_complet
                    FROM {$this->table_etudiants} e
                    JOIN users u ON e.user_id = u.id
                    JOIN {$this->table_inscriptions} i ON e.id = i.etudiant_id
                    WHERE i.classe_id = :classe_id
                    ORDER BY e.nom, e.prenom";
        } else {
            $sql = "SELECT e.id, e.matricule, e.nom, e.prenom, 
                           CONCAT(e.nom, ' ', e.prenom) as nom_complet
                    FROM {$this->table_etudiants} e
                    JOIN {$this->table_inscriptions} i ON e.id = i.etudiant_id
                    WHERE i.classe_id = :classe_id
                    ORDER BY e.nom, e.prenom";
        }
        
        return $this->db->fetchAll($sql, ['classe_id' => $this->classe_id]);
    }
    
    /**
     * Récupère la liste des professeurs de la classe
     * @return array Liste des professeurs avec leurs matières
     */
    public function getListeProfesseurs() {
        if (!$this->classe_id) {
            return [];
        }
        
        $sql = "SELECT p.*, u.email, 
                       GROUP_CONCAT(m.intitule SEPARATOR ', ') as matieres,
                       CONCAT(p.nom, ' ', p.prenom) as nom_complet,
                       p.id AS professeur_id,
                       MIN(a.matiere_id) AS matiere_id
                FROM {$this->table_professeurs} p
                JOIN users u ON p.user_id = u.id
                JOIN {$this->table_affectation} a ON p.id = a.professeur_id
                JOIN matieres m ON a.matiere_id = m.id
                WHERE a.classe_id = :classe_id
                GROUP BY p.id
                ORDER BY p.nom, p.prenom";
        
        return $this->db->fetchAll($sql, ['classe_id' => $this->classe_id]);
    }
    
    /**
     * Ajoute un étudiant à la classe
     * @param array $etudiantData Les données de l'étudiant
     * @return int|false L'ID de l'inscription ou false en cas d'échec
     */
    public function ajouterEtudiant($etudiantData) {
        try {
            $this->db->beginTransaction();
            
            // Vérifier si l'étudiant existe déjà
            $etudiant = $this->db->fetch(
                "SELECT id FROM {$this->table_etudiants} WHERE matricule = :matricule",
                ['matricule' => $etudiantData['matricule']]
            );
            
            if ($etudiant) {
                $etudiantId = $etudiant['id'];
            } else {
                // Créer un nouvel étudiant
                $etudiant = new Etudiant();
                $etudiantId = $etudiant->creerEtudiant([
                    'matricule' => $etudiantData['matricule'],
                    'nom' => $etudiantData['nom'],
                    'prenom' => $etudiantData['prenom'],
                    'date_naissance' => $etudiantData['date_naissance'] ?? null,
                    'lieu_naissance' => $etudiantData['lieu_naissance'] ?? null,
                    'telephone' => $etudiantData['telephone'] ?? null,
                    'email' => $etudiantData['email'] ?? null,
                    'password' => 'SAMA2007' // Mot de passe par défaut pour les étudiants
                ]);
                
                if (!$etudiantId) {
                    throw new Exception("Échec de la création de l'étudiant");
                }
            }
            
            // Vérifier si l'étudiant n'est pas déjà inscrit dans cette classe
            $dejaInscrit = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->table_inscriptions} 
                 WHERE etudiant_id = :etudiant_id AND classe_id = :classe_id",
                [
                    'etudiant_id' => $etudiantId,
                    'classe_id' => $this->classe_id
                ]
            );
            
            if ($dejaInscrit) {
                throw new Exception("L'étudiant est déjà inscrit dans cette classe");
            }
            
            // Récupérer l'année universitaire active
            $anneeActive = $this->db->fetch(
                "SELECT id FROM annees_universitaires WHERE est_active = 1 LIMIT 1"
            );

            if (!$anneeActive) {
                throw new Exception("Aucune année universitaire active trouvée");
            }

            // Inscrire l'étudiant à la classe
            $inscriptionId = $this->db->insert(
                "INSERT INTO {$this->table_inscriptions} 
                (etudiant_id, classe_id, annee_universitaire_id, date_inscription) 
                VALUES 
                (:etudiant_id, :classe_id, :annee_id, NOW())",
                [
                    'etudiant_id' => $etudiantId,
                    'classe_id' => $this->classe_id,
                    'annee_id' => $anneeActive['id']
                ]
            );
            
            $this->db->commit();
            return $inscriptionId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur ajout étudiant: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprime un étudiant de la classe
     * @param int $etudiantId ID de l'étudiant à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function supprimerEtudiant($etudiantId) {
        try {
            // Vérifier que l'étudiant appartient bien à la classe
            $appartient = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->table_inscriptions} 
                 WHERE etudiant_id = :etudiant_id AND classe_id = :classe_id",
                [
                    'etudiant_id' => $etudiantId,
                    'classe_id' => $this->classe_id
                ]
            );
            
            if (!$appartient) {
                throw new Exception("L'étudiant n'appartient pas à cette classe");
            }
            
            // Supprimer l'inscription
            $this->db->execute(
                "DELETE FROM {$this->table_inscriptions} 
                 WHERE etudiant_id = :etudiant_id AND classe_id = :classe_id",
                [
                    'etudiant_id' => $etudiantId,
                    'classe_id' => $this->classe_id
                ]
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur suppression étudiant: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajoute un nouveau professeur à la classe avec création du compte
     * @param array $professeurData Les données du professeur
     * @return bool True si l'ajout a réussi, false sinon
     */
    public function ajouterNouveauProfesseur($professeurData) {
        try {
            $this->db->beginTransaction();
            
            // Vérifier si l'email existe déjà
            $professeurExistant = $this->db->fetch(
                "SELECT id FROM {$this->table_professeurs} WHERE email = :email",
                ['email' => $professeurData['email']]
            );
            
            if ($professeurExistant) {
                $professeurId = $professeurExistant['id'];
            } else {
                // Créer un nouveau professeur
                $professeur = new Professeur();
                $professeurId = $professeur->creerProfesseur([
                    'nom' => $professeurData['nom'],
                    'prenom' => $professeurData['prenom'],
                    'email' => $professeurData['email'],
                    'telephone' => $professeurData['telephone'],
                    'password' => $professeurData['telephone'] // Mot de passe par défaut = téléphone
                ]);
                
                if (!$professeurId) {
                    throw new Exception("Échec de la création du professeur");
                }
            }
            
            // Vérifier que la matière n'est pas déjà attribuée
            $dejaAffecte = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->table_affectation} 
                 WHERE classe_id = :classe_id AND matiere_id = :matiere_id",
                [
                    'classe_id' => $this->classe_id,
                    'matiere_id' => $professeurData['matiere_id']
                ]
            );
            
            if ($dejaAffecte) {
                throw new Exception("Cette matière est déjà attribuée à un professeur pour cette classe");
            }
            
            // Récupérer l'année universitaire active
            $anneeActive = $this->db->fetch(
                "SELECT id FROM annees_universitaires WHERE est_active = 1 LIMIT 1"
            );
            
            if (!$anneeActive) {
                throw new Exception("Aucune année universitaire active trouvée");
            }
            
            // Ajouter l'affectation
            $this->db->insert(
                "INSERT INTO {$this->table_affectation} 
                (professeur_id, matiere_id, classe_id, annee_universitaire_id) 
                VALUES 
                (:professeur_id, :matiere_id, :classe_id, :annee_id)",
                [
                    'professeur_id' => $professeurId,
                    'matiere_id' => $professeurData['matiere_id'],
                    'classe_id' => $this->classe_id,
                    'annee_id' => $anneeActive['id']
                ]
            );
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur ajout professeur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajoute un professeur existant à la classe pour une matière donnée
     * @param int $professeurId ID du professeur
     * @param int $matiereId ID de la matière
     * @return bool True si l'ajout a réussi, false sinon
     */
    public function ajouterProfesseur($professeurId, $matiereId) {
        try {
            // Vérifier que la matière n'est pas déjà attribuée à un autre professeur
            $dejaAffecte = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->table_affectation} 
                 WHERE classe_id = :classe_id AND matiere_id = :matiere_id",
                [
                    'classe_id' => $this->classe_id,
                    'matiere_id' => $matiereId
                ]
            );
            
            if ($dejaAffecte) {
                throw new Exception("Cette matière est déjà attribuée à un professeur pour cette classe");
            }
            
            // Récupérer l'année universitaire active
            $anneeActive = $this->db->fetch(
                "SELECT id FROM annees_universitaires WHERE est_active = 1 LIMIT 1"
            );
            
            if (!$anneeActive) {
                throw new Exception("Aucune année universitaire active trouvée");
            }
            
            // Ajouter l'affectation
            $this->db->insert(
                "INSERT INTO {$this->table_affectation} 
                (professeur_id, matiere_id, classe_id, annee_universitaire_id) 
                VALUES 
                (:professeur_id, :matiere_id, :classe_id, :annee_id)",
                [
                    'professeur_id' => $professeurId,
                    'matiere_id' => $matiereId,
                    'classe_id' => $this->classe_id,
                    'annee_id' => $anneeActive['id']
                ]
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur ajout professeur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprime un professeur de la classe pour une matière donnée
     * @param int $professeurId ID du professeur
     * @param int $matiereId ID de la matière
     * @return bool True si la suppression a réussi, false sinon
     */
    public function supprimerProfesseur($professeurId, $matiereId) {
        try {
            $this->db->execute(
                "DELETE FROM {$this->table_affectation} 
                 WHERE professeur_id = :professeur_id 
                 AND matiere_id = :matiere_id 
                 AND classe_id = :classe_id",
                [
                    'professeur_id' => $professeurId,
                    'matiere_id' => $matiereId,
                    'classe_id' => $this->classe_id
                ]
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur suppression professeur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Soumet les listes d'étudiants et de professeurs pour validation
     * @return bool True si la soumission a réussi, false sinon
     */
    public function soumettreListes() {
        try {
            // Vérifier que les listes ne sont pas vides
            $nbEtudiants = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->table_inscriptions} 
                 WHERE classe_id = :classe_id",
                ['classe_id' => $this->classe_id]
            );
            
            $nbProfesseurs = $this->db->fetchColumn(
                "SELECT COUNT(DISTINCT professeur_id) 
                 FROM {$this->table_affectation} 
                 WHERE classe_id = :classe_id",
                ['classe_id' => $this->classe_id]
            );
            
            if ($nbEtudiants == 0) {
                throw new Exception("La liste des étudiants ne peut pas être vide");
            }
            
            if ($nbProfesseurs == 0) {
                throw new Exception("La liste des professeurs ne peut pas être vide");
            }
            
            // Marquer la classe comme prête pour validation
            $this->db->execute(
                "UPDATE {$this->table_classes} 
                 SET statut_listes = 'en_attente'
                 WHERE id = :id",
                ['id' => $this->classe_id]
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur soumission des listes: " . $e->getMessage());
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['error'] = $e->getMessage();
            }
            return false;
        }
    }
    
    /**
     * Récupère l'historique des modifications des listes
     * @return array Liste des modifications
     */
    public function getHistoriqueModifications() {
        // Implémentation à compléter selon la structure de la table d'historique
        return [];
    }
    
    // Getters et Setters
    public function getClasseId() {
        return $this->classe_id;
    }
    
    public function getDateNomination() {
        return $this->date_nomination;
    }
    
    public function getClasseNom() {
        return $this->classe_nom ?? null;
    }
    
    public function getClasseNiveau() {
        return $this->classe_niveau ?? null;
    }
}
