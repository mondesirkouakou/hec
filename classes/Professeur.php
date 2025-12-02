<?php
/**
 * Classe pour la gestion des professeurs
 * Étend la classe User pour ajouter des fonctionnalités spécifiques aux professeurs
 */
class Professeur extends User {
    // Propriétés spécifiques aux professeurs
    protected $matieres_enseignees = [];
    protected $telephone;
    protected $specialite;
    
    // Table dans la base de données
    protected $table_professeurs = 'professeurs';
    protected $table_matieres = 'matieres';
    protected $table_affectation = 'affectation_professeur';

    /**
     * Crée un nouveau professeur
     * @param array $professeurData Les données du professeur
     * @return int|false L'ID du nouveau professeur ou false en cas d'échec
     */
    public function creerProfesseur($professeurData) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();

            // 1. Créer d'abord l'utilisateur de base
            $userData = [
                'username' => $professeurData['email'], // L'email comme nom d'utilisateur
                'email' => $professeurData['email'],
                'password' => $professeurData['password'] ?? $professeurData['telephone'], // Téléphone comme mot de passe par défaut
                'role_id' => 3 // Rôle professeur
            ];

            $userId = parent::create($userData);

            if (!$userId) {
                throw new Exception("Échec de la création de l'utilisateur professeur");
            }

            // 2. Créer l'entrée dans la table des professeurs
            $sql = "INSERT INTO {$this->table_professeurs} 
                    (user_id, nom, prenom, email, telephone, specialite)
                    VALUES (:user_id, :nom, :prenom, :email, :telephone, :specialite)";

            $params = [
                'user_id' => $userId,
                'nom' => $professeurData['nom'],
                'prenom' => $professeurData['prenom'],
                'email' => $professeurData['email'],
                'telephone' => $professeurData['telephone'],
                'specialite' => $professeurData['specialite'] ?? null
            ];

            $professeurId = $this->db->insert($sql, $params);

            if (!$professeurId) {
                throw new Exception("Échec de la création du profil professeur");
            }

            // 3. Ajouter les matières enseignées si fournies
            if (!empty($professeurData['matieres']) && is_array($professeurData['matieres'])) {
                $this->ajouterMatieres($professeurId, $professeurData['matieres']);
            }

            // Valider la transaction
            $this->db->commit();

            return $professeurId;

        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollBack();
            error_log("Erreur création professeur: " . $e->getMessage());
            return false;
        }
    }

    public function count() {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table_professeurs}");
    }

    /**
     * Ajoute des matières enseignées par un professeur
     * @param int $professeurId ID du professeur
     * @param array $matieresIds Tableau des IDs des matières
     * @return bool True si l'ajout a réussi, false sinon
     */
    public function ajouterMatieres($professeurId, $matieresIds) {
        try {
            $this->db->beginTransaction();
            
            // Supprimer d'abord les anciennes affectations
            $this->db->execute(
                "DELETE FROM {$this->table_affectation} WHERE professeur_id = :professeur_id",
                ['professeur_id' => $professeurId]
            );
            
            // Ajouter les nouvelles affectations
            $sql = "INSERT INTO {$this->table_affectation} 
                    (professeur_id, matiere_id, annee_universitaire_id) 
                    VALUES (:professeur_id, :matiere_id, :annee_id)";
            
            // Récupérer l'année universitaire active
            $anneeActive = $this->getAnneeUniversitaireActive();
            
            if (!$anneeActive) {
                throw new Exception("Aucune année universitaire active trouvée");
            }
            
            foreach ($matieresIds as $matiereId) {
                $this->db->insert($sql, [
                    'professeur_id' => $professeurId,
                    'matiere_id' => $matiereId,
                    'annee_id' => $anneeActive['id']
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur ajout matières: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère l'année universitaire active
     * @return array|false Les données de l'année active ou false si non trouvée
     */
    private function getAnneeUniversitaireActive() {
        return $this->db->fetch(
            "SELECT * FROM annees_universitaires WHERE est_active = 1 LIMIT 1"
        );
    }

    /**
     * Récupère un professeur par son ID utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @return array|false Les données du professeur ou false si non trouvé
     */
    public function getByUserId($userId) {
        $sql = "SELECT p.*, u.email, u.is_active, u.first_login 
                FROM {$this->table_professeurs} p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.user_id = :user_id";
        
        $professeur = $this->db->fetch($sql, ['user_id' => $userId]);
        
        if ($professeur) {
            $professeur['matieres'] = $this->getMatieresEnseignees($professeur['id']);
        }
        
        return $professeur;
    }
    
    /**
     * Récupère les matières enseignées par un professeur
     * @param int $professeurId ID du professeur
     * @return array Liste des matières enseignées
     */
    public function getMatieresEnseignees($professeurId) {
        $sql = "SELECT m.* 
                FROM {$this->table_matieres} m
                JOIN {$this->table_affectation} a ON m.id = a.matiere_id
                WHERE a.professeur_id = :professeur_id";
                
        return $this->db->fetchAll($sql, ['professeur_id' => $professeurId]);
    }

    /**
     * Récupère les classes d'un professeur pour une matière donnée
     * @param int $professeurId ID du professeur
     * @param int $matiereId ID de la matière
     * @return array Liste des classes
     */
    public function getClassesPourMatiere($professeurId, $matiereId) {
        $sql = "SELECT DISTINCT c.* 
                FROM classes c
                JOIN {$this->table_affectation} a ON c.id = a.classe_id
                WHERE a.professeur_id = :professeur_id 
                AND a.matiere_id = :matiere_id
                AND c.statut_listes = 'validee'";
                
        return $this->db->fetchAll($sql, [
            'professeur_id' => $professeurId,
            'matiere_id' => $matiereId
        ]);
    }

    public function getClassesAssociees($professeurId) {
        $sql = "SELECT DISTINCT c.* 
                FROM classes c
                JOIN {$this->table_affectation} a ON c.id = a.classe_id
                WHERE a.professeur_id = :professeur_id
                  AND c.statut_listes = 'validee'
                ORDER BY c.intitule";
        return $this->db->fetchAll($sql, ['professeur_id' => $professeurId]);
    }

    /**
     * Met à jour les informations d'un professeur
     * @param int $professeurId ID du professeur
     * @param array $professeurData Nouvelles données
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateProfesseur($professeurId, $professeurData) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();

            // 1. Récupérer l'ID utilisateur
            $professeur = $this->getById($professeurId);
            
            if (!$professeur) {
                throw new Exception("Professeur non trouvé");
            }

            // 2. Mettre à jour les informations de base de l'utilisateur
            $userUpdate = [];
            if (!empty($professeurData['email'])) {
                $userUpdate['email'] = $professeurData['email'];
                $userUpdate['username'] = $professeurData['email']; // Mettre à jour aussi le nom d'utilisateur
            }
            
            if (!empty($professeurData['password'])) {
                $userUpdate['password'] = $professeurData['password'];
            }
            
            if (!empty($userUpdate)) {
                parent::update($professeur['user_id'], $userUpdate);
            }

            // 3. Mettre à jour les informations spécifiques au professeur
            $updates = [];
            $params = ['id' => $professeurId];

            $fields = ['nom', 'prenom', 'telephone', 'specialite'];
            
            foreach ($fields as $field) {
                if (isset($professeurData[$field])) {
                    $updates[] = "$field = :$field";
                    $params[$field] = $professeurData[$field];
                }
            }

            if (!empty($updates)) {
                $sql = "UPDATE {$this->table_professeurs} SET " . implode(', ', $updates) . " WHERE id = :id";
                $this->db->execute($sql, $params);
            }

            // 4. Mettre à jour les matières enseignées si fournies
            if (isset($professeurData['matieres']) && is_array($professeurData['matieres'])) {
                $this->ajouterMatieres($professeurId, $professeurData['matieres']);
            }

            // Valider la transaction
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollBack();
            error_log("Erreur mise à jour professeur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère la liste des professeurs avec pagination
     * @param int $page Numéro de la page
     * @param int $perPage Nombre de professeurs par page
     * @return array Tableau contenant les professeurs et les informations de pagination
     */
    public function getProfesseursPagines($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        // Récupérer les professeurs
        $sql = "SELECT p.*, u.email, u.is_active, 
                       CONCAT(p.nom, ' ', p.prenom) as nom_complet
                FROM {$this->table_professeurs} p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.nom, p.prenom
                LIMIT :offset, :perPage";
        
        $professeurs = $this->db->fetchAll($sql, [
            'offset' => $offset,
            'perPage' => $perPage
        ]);
        
        // Compter le nombre total de professeurs
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table_professeurs}");
        
        return [
            'professeurs' => $professeurs,
            'total' => (int)$count,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($count / $perPage)
        ];
    }

    /**
     * Récupère les étudiants d'une classe pour une matière donnée
     * @param int $classeId ID de la classe
     * @param int $matiereId ID de la matière
     * @return array Liste des étudiants avec leurs notes éventuelles
     */
    public function getEtudiantsPourNote($classeId, $matiereId) {
        $sql = "SELECT e.id, e.matricule, e.nom, e.prenom,
                       n.note, n.appreciation, n.statut,
                       n.note1, n.note2, n.note3, n.note4, n.note5
                FROM etudiants e
                JOIN inscriptions i ON e.id = i.etudiant_id
                LEFT JOIN notes n ON e.id = n.etudiant_id
                                  AND n.matiere_id = :matiere_id
                                  AND n.classe_id = :classe_id
                WHERE i.classe_id = :classe_id_join
                ORDER BY e.nom, e.prenom";

        return $this->db->fetchAll($sql, [
            'classe_id' => $classeId,
            'matiere_id' => $matiereId,
            'classe_id_join' => $classeId
        ]);
    }

    /**
     * Enregistre ou met à jour les notes des étudiants
     * @param int $professeurId ID du professeur (utilisé pour saisie_par)
     * @param array $notesData Données des notes (etudiant_id, matiere_id, classe_id, semestre_id, note, appreciation)
     * @return bool True si l'enregistrement a réussi, false sinon
     */
    public function enregistrerNotes($professeurId, $notesData) {
        try {
            $this->db->beginTransaction();

            $currentDate = date('Y-m-d H:i:s');

            foreach ($notesData as $note) {
                // Vérifier si une note existe déjà pour cet étudiant / matière / classe / semestre
                $existingNote = $this->db->fetch(
                    "SELECT id FROM notes 
                     WHERE etudiant_id = :etudiant_id 
                       AND matiere_id = :matiere_id 
                       AND classe_id = :classe_id 
                       AND semestre_id = :semestre_id",
                    [
                        'etudiant_id' => $note['etudiant_id'],
                        'matiere_id' => $note['matiere_id'],
                        'classe_id' => $note['classe_id'],
                        'semestre_id' => $note['semestre_id'],
                    ]
                );

                if ($existingNote) {
                    // Mise à jour de la note existante (on ne touche qu'aux champs utiles)
                    $sql = "UPDATE notes SET 
                                note = :note,
                                note1 = :note1,
                                note2 = :note2,
                                note3 = :note3,
                                note4 = :note4,
                                note5 = :note5,
                                appreciation = :appreciation,
                                statut = :statut
                            WHERE id = :id";

                    $params = [
                        'id' => $existingNote['id'],
                        'note' => $note['note'],
                        'note1' => $note['note1'] ?? null,
                        'note2' => $note['note2'] ?? null,
                        'note3' => $note['note3'] ?? null,
                        'note4' => $note['note4'] ?? null,
                        'note5' => $note['note5'] ?? null,
                        'appreciation' => $note['appreciation'] ?? null,
                        'statut' => 'soumis',
                    ];

                    $this->db->execute($sql, $params);
                } else {
                    // Insertion d'une nouvelle note alignée sur la structure de la table
                    $sql = "INSERT INTO notes 
                            (etudiant_id, matiere_id, classe_id, semestre_id, note, note1, note2, note3, note4, note5, appreciation, statut, saisie_par, date_saisie)
                            VALUES 
                            (:etudiant_id, :matiere_id, :classe_id, :semestre_id, :note, :note1, :note2, :note3, :note4, :note5, :appreciation, :statut, :saisie_par, :date_saisie)";

                    $params = [
                        'etudiant_id' => $note['etudiant_id'],
                        'matiere_id' => $note['matiere_id'],
                        'classe_id' => $note['classe_id'],
                        'semestre_id' => $note['semestre_id'],
                        'note' => $note['note'],
                        'note1' => $note['note1'] ?? null,
                        'note2' => $note['note2'] ?? null,
                        'note3' => $note['note3'] ?? null,
                        'note4' => $note['note4'] ?? null,
                        'note5' => $note['note5'] ?? null,
                        'appreciation' => $note['appreciation'] ?? null,
                        'statut' => 'soumis',
                        'saisie_par' => $professeurId,
                        'date_saisie' => $currentDate,
                    ];

                    $this->db->insert($sql, $params);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur enregistrement notes: " . $e->getMessage());
            return false;
        }
    }

    public function setTelephone($telephone) {
        $this->telephone = $telephone;
        return $this;
    }

    public function getSpecialite() {
        return $this->specialite;
    }

    public function setSpecialite($specialite) {
        $this->specialite = $specialite;
        return $this;
    }
    
    public function getMatieresEnseigneesList() {
        return $this->matieres_enseignees;
    }
    
    public function setMatieresEnseignees($matieres) {
        $this->matieres_enseignees = $matieres;
        return $this;
    }
}
