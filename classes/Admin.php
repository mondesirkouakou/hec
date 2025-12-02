<?php
/**
 * Classe pour la gestion des administrateurs
 * Étend la classe User pour ajouter des fonctionnalités administratives
 */
class Admin extends User {
    // Tables de la base de données
    protected $table_annees = 'annees_universitaires';
    protected $table_semestres = 'semestres';
    protected $table_classes = 'classes';
    protected $table_etudiants = 'etudiants';
    protected $table_professeurs = 'professeurs';
    protected $table_matieres = 'matieres';
    protected $table_affectation = 'affectation_professeur';
    protected $table_notes = 'notes';
    protected $table_chef_classe = 'chef_classe';

    /**
     * Crée une nouvelle année universitaire
     * @param array $anneeData Les données de l'année (annee_debut, annee_fin)
     * @return int|false L'ID de la nouvelle année ou false en cas d'échec
     */
    public function creerAnneeUniversitaire($anneeData) {
        try {
            // Désactiver toutes les autres années
            $this->db->execute("UPDATE {$this->table_annees} SET est_active = 0");
            
            // Créer la nouvelle année
            $sql = "INSERT INTO {$this->table_annees} (annee_debut, annee_fin, est_active) 
                    VALUES (:annee_debut, :annee_fin, 1)";
            
            $anneeId = $this->db->insert($sql, [
                'annee_debut' => $anneeData['annee_debut'],
                'annee_fin' => $anneeData['annee_fin']
            ]);
            
            if (!$anneeId) {
                throw new Exception("Échec de la création de l'année universitaire");
            }
            
            // Créer les semestres pour cette année
            $this->creerSemestres($anneeId);
            
            return $anneeId;
            
        } catch (Exception $e) {
            error_log("Erreur création année universitaire: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crée les semestres pour une année universitaire
     * @param int $anneeId ID de l'année universitaire
     * @return bool True si la création a réussi, false sinon
     */
    private function creerSemestres($anneeId) {
        try {
            // Semestre 1 (septembre à janvier)
            $this->db->insert(
                "INSERT INTO {$this->table_semestres} 
                (annee_universitaire_id, numero, date_debut, date_fin, est_ouvert) 
                VALUES 
                (:annee_id, 1, :debut_s1, :fin_s1, 1)",
                [
                    'annee_id' => $anneeId,
                    'debut_s1' => date('Y-09-01'),
                    'fin_s1' => date('Y-01-31', strtotime('+1 year'))
                ]
            );
            
            // Semestre 2 (février à juin)
            $this->db->insert(
                "INSERT INTO {$this->table_semestres} 
                (annee_universitaire_id, numero, date_debut, date_fin, est_ouvert) 
                VALUES 
                (:annee_id, 2, :debut_s2, :fin_s2, 0)",
                [
                    'annee_id' => $anneeId,
                    'debut_s2' => date('Y-02-01', strtotime('+1 year')),
                    'fin_s2' => date('Y-06-30', strtotime('+1 year'))
                ]
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur création des semestres: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ouvre ou ferme un semestre
     * @param int $semestreId ID du semestre
     * @param bool $estOuvert True pour ouvrir, false pour fermer
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function gererSemestre($semestreId, $estOuvert) {
        try {
            $sql = "UPDATE {$this->table_semestres} 
                    SET est_ouvert = :est_ouvert, 
                        est_cloture = :est_cloture
                    WHERE id = :id";
            
            return $this->db->execute($sql, [
                'id' => $semestreId,
                'est_ouvert' => $estOuvert ? 1 : 0,
                'est_cloture' => $estOuvert ? 0 : 1
            ]) > 0;
            
        } catch (Exception $e) {
            error_log("Erreur gestion semestre: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crée une nouvelle classe
     * @param array $classeData Les données de la classe
     * @return int|false L'ID de la nouvelle classe ou false en cas d'échec
     */
    public function creerClasse($classeData) {
        try {
            $sql = "INSERT INTO {$this->table_classes} 
                    (code, intitule, niveau, annee_universitaire_id) 
                    VALUES 
                    (:code, :intitule, :niveau, :annee_id)";
            
            // Récupérer l'année universitaire active
            $anneeActive = $this->getAnneeActive();
            
            if (!$anneeActive) {
                throw new Exception("Aucune année universitaire active trouvée");
            }
            
            $classeId = $this->db->insert($sql, [
                'code' => $classeData['code'],
                'intitule' => $classeData['intitule'],
                'niveau' => $classeData['niveau'],
                'annee_id' => $anneeActive['id']
            ]);
            
            return $classeId;
            
        } catch (Exception $e) {
            error_log("Erreur création classe: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère l'année universitaire active
     * @return array|false Les données de l'année active ou false si non trouvée
     */
    public function getAnneeActive() {
        return $this->db->fetch(
            "SELECT * FROM {$this->table_annees} WHERE est_active = 1 LIMIT 1"
        );
    }
    
    /**
     * Désigne un étudiant comme chef de classe
     * @param int $etudiantId ID de l'étudiant
     * @param int $classeId ID de la classe
     * @return bool True si la désignation a réussi, false sinon
     */
    public function designerChefClasse($etudiantId, $classeId, $email = null, $password = null) {
        try {
            $this->db->beginTransaction();
            
            // Vérifier si l'étudiant appartient bien à la classe
            $appartient = $this->db->fetch(
                "SELECT id FROM inscriptions 
                 WHERE etudiant_id = :etudiant_id 
                 AND classe_id = :classe_id",
                ['etudiant_id' => $etudiantId, 'classe_id' => $classeId]
            );
            
            if (!$appartient) {
                throw new Exception("L'étudiant n'appartient pas à cette classe");
            }

            // Récupérer les infos de l'étudiant
            $etudiant = $this->db->fetch(
                "SELECT e.id, e.matricule, e.user_id FROM {$this->table_etudiants} e WHERE e.id = :id",
                ['id' => $etudiantId]
            );
            if (!$etudiant) {
                throw new Exception("Étudiant introuvable");
            }

            // Créer ou mettre à jour le compte utilisateur
            if (!empty($etudiant['user_id'])) {
                $existing = $this->db->fetch("SELECT id, email FROM users WHERE id = :id", ['id' => $etudiant['user_id']]);
                if (!$existing) {
                    throw new Exception("Compte utilisateur introuvable");
                }
                if (!is_null($email)) {
                    $other = $this->db->fetch("SELECT id FROM users WHERE email = :email AND id != :id", ['email' => $email, 'id' => $etudiant['user_id']]);
                    if ($other) {
                        throw new Exception("Cet email est déjà utilisé par un autre compte");
                    }
                }
                $set = ['role_id = 2', 'is_active = 1'];
                $params = ['id' => $etudiant['user_id']];
                if (!is_null($email)) { $set[] = 'email = :email'; $params['email'] = $email; }
                if (!is_null($password)) { $set[] = 'password = :password'; $params['password'] = password_hash($password, PASSWORD_BCRYPT); $set[] = 'first_login = 1'; }
                $sql = "UPDATE users SET " . implode(', ', $set) . " WHERE id = :id";
                $this->db->execute($sql, $params);
                $userId = (int)$etudiant['user_id'];
            } else {
                if (is_null($email) || is_null($password)) {
                    throw new Exception("Email et mot de passe requis pour créer le compte chef de classe");
                }
                $username = $etudiant['matricule'];
                $userId = $this->create([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'role_id' => 2
                ]);
                if (!$userId) {
                    throw new Exception("Échec de la création du compte utilisateur");
                }
                $this->db->execute(
                    "UPDATE {$this->table_etudiants} SET user_id = :user_id WHERE id = :id",
                    ['user_id' => $userId, 'id' => $etudiantId]
                );
            }
            
            // Récupérer la classe pour son année universitaire
            $classe = $this->db->fetch("SELECT id, annee_universitaire_id FROM {$this->table_classes} WHERE id = :id", ['id' => $classeId]);
            if (!$classe || empty($classe['annee_universitaire_id'])) {
                throw new Exception("Classe introuvable ou année universitaire non définie");
            }

            // Supprimer l'ancien chef de classe s'il existe
            $this->db->execute(
                "DELETE FROM {$this->table_chef_classe} 
                 WHERE classe_id = :classe_id",
                ['classe_id' => $classeId]
            );
            
            // Désigner le nouveau chef de classe
            $this->db->insert(
                "INSERT INTO {$this->table_chef_classe} 
                (etudiant_id, classe_id, annee_universitaire_id, date_nomination) 
                VALUES 
                (:etudiant_id, :classe_id, :annee_universitaire_id, NOW())",
                [
                    'etudiant_id' => $etudiantId,
                    'classe_id' => $classeId,
                    'annee_universitaire_id' => $classe['annee_universitaire_id']
                ]
            );
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = $e->getMessage();
            error_log("Erreur désignation chef de classe: " . $e->getMessage());
            return false;
        }
    }

    public function creerChefClassePourClasse($classeId, $email, $password) {
        try {
            $this->db->beginTransaction();

            $classe = $this->db->fetch("SELECT id, code, annee_universitaire_id FROM {$this->table_classes} WHERE id = :id", ['id' => $classeId]);
            if (!$classe) {
                throw new Exception("Classe introuvable");
            }
            if (empty($classe['annee_universitaire_id'])) {
                throw new Exception("Année universitaire non définie pour cette classe");
            }

            $baseUsername = strstr($email, '@', true) ?: ('chef_' . $classe['code']);
            $username = $baseUsername;
            if ($this->usernameExists($username)) {
                $username = $baseUsername . '_' . $classeId . '_' . time();
            }

            $userId = $this->create([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role_id' => 2
            ]);
            if (!$userId) {
                throw new Exception("Échec de la création du compte utilisateur");
            }

            $matricule = 'CHC-' . $classeId . '-' . $userId;
            $etudiantId = (int)$this->db->insert(
                "INSERT INTO {$this->table_etudiants} (user_id, matricule, nom, prenom, date_naissance, lieu_naissance, telephone) VALUES (:user_id, :matricule, :nom, :prenom, :date_naissance, :lieu_naissance, :telephone)",
                [
                    'user_id' => $userId,
                    'matricule' => $matricule,
                    'nom' => 'Chef',
                    'prenom' => 'Classe',
                    'date_naissance' => null,
                    'lieu_naissance' => null,
                    'telephone' => null
                ]
            );

            $this->db->execute(
                "DELETE FROM {$this->table_chef_classe} WHERE classe_id = :classe_id",
                ['classe_id' => $classeId]
            );

            $this->db->insert(
                "INSERT INTO {$this->table_chef_classe} (etudiant_id, classe_id, annee_universitaire_id, date_nomination) VALUES (:etudiant_id, :classe_id, :annee_universitaire_id, NOW())",
                [
                    'etudiant_id' => $etudiantId,
                    'classe_id' => $classeId,
                    'annee_universitaire_id' => $classe['annee_universitaire_id']
                ]
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = $e->getMessage();
            error_log("Erreur création chef de classe: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valide les listes d'étudiants et de professeurs soumises par un chef de classe
     * @param int $classeId ID de la classe
     * @return bool True si la validation a réussi, false sinon
     */
    public function validerListesClasse($classeId) {
        try {
            $this->db->execute(
                "UPDATE {$this->table_classes} 
                 SET statut_listes = 'validee' 
                 WHERE id = :id AND statut_listes = 'en_attente'",
                ['id' => $classeId]
            );

            return true;
            
        } catch (Exception $e) {
            error_log("Erreur validation listes de classe: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valide en une seule opération toutes les listes de classes actuellement en attente
     * @return bool True si la validation a réussi, false sinon
     */
    public function validerToutesLesListesEnAttente() {
        try {
            $this->db->execute(
                "UPDATE {$this->table_classes} 
                 SET statut_listes = 'validee' 
                 WHERE statut_listes = 'en_attente'"
            );
            return true;
        } catch (Exception $e) {
            error_log("Erreur validation de toutes les listes de classes en attente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère la liste des chefs de classe avec leurs comptes utilisateurs
     * @return array
     */
    public function getChefsClasseAvecComptes() {
        $sql = "SELECT cc.id AS chef_classe_id,
                       cc.date_nomination,
                       c.id AS classe_id,
                       c.code AS classe_code,
                       c.intitule AS classe_intitule,
                       e.id AS etudiant_id,
                       e.matricule,
                       e.nom,
                       e.prenom,
                       u.id AS user_id,
                       u.email,
                       u.is_active
                FROM {$this->table_chef_classe} cc
                JOIN {$this->table_etudiants} e ON cc.etudiant_id = e.id
                JOIN {$this->table_classes} c ON cc.classe_id = c.id
                JOIN users u ON e.user_id = u.id
                ORDER BY c.code, e.nom, e.prenom";
        return $this->db->fetchAll($sql);
    }

    /**
     * Active ou désactive en masse des comptes chef de classe
     * @param int[] $userIds
     * @param bool $isActive
     * @return bool
     */
    public function changerStatutChefsClasse(array $userIds, $isActive) {
        if (empty($userIds)) {
            return false;
        }
        try {
            foreach ($userIds as $userId) {
                $this->db->execute(
                    "UPDATE users SET is_active = :is_active WHERE id = :id",
                    [
                        'is_active' => $isActive ? 1 : 0,
                        'id' => (int)$userId
                    ]
                );
            }
            return true;
        } catch (Exception $e) {
            error_log("Erreur changement de statut des chefs de classe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime les droits chef de classe et désactive les comptes sélectionnés
     * @param int[] $userIds
     * @return bool
     */
    public function supprimerChefsClasse(array $userIds) {
        if (empty($userIds)) {
            return false;
        }
        try {
            $this->db->beginTransaction();
            foreach ($userIds as $userId) {
                $userId = (int)$userId;

                // Récupérer l'étudiant lié à ce compte
                $etudiant = $this->db->fetch(
                    "SELECT id FROM {$this->table_etudiants} WHERE user_id = :user_id",
                    ['user_id' => $userId]
                );

                if ($etudiant) {
                    // Supprimer la désignation de chef de classe
                    $this->db->execute(
                        "DELETE FROM {$this->table_chef_classe} WHERE etudiant_id = :etudiant_id",
                        ['etudiant_id' => $etudiant['id']]
                    );

                    // Détacher le compte utilisateur de l'étudiant
                    $this->db->execute(
                        "UPDATE {$this->table_etudiants} SET user_id = NULL WHERE id = :etudiant_id",
                        ['etudiant_id' => $etudiant['id']]
                    );
                }

                // Supprimer complètement le compte utilisateur
                $this->db->execute(
                    "DELETE FROM users WHERE id = :id",
                    ['id' => $userId]
                );
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur suppression des chefs de classe: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Saisit les notes des étudiants pour une classe et une matière
     * @param array $notesData Tableau des notes à enregistrer
     * @return bool True si la saisie a réussi, false sinon
     */
    public function saisirNotes($notesData) {
        try {
            $this->db->beginTransaction();
            
            foreach ($notesData as $note) {
                $sql = "INSERT INTO {$this->table_notes} 
                        (etudiant_id, matiere_id, note, statut, saisie_par, date_saisie)
                        VALUES 
                        (:etudiant_id, :matiere_id, :note, 'saisie', :saisie_par, NOW())
                        ON DUPLICATE KEY UPDATE 
                        note = VALUES(note),
                        statut = 'saisie',
                        date_modification = NOW()";
                
                $this->db->execute($sql, [
                    'etudiant_id' => $note['etudiant_id'],
                    'matiere_id' => $note['matiere_id'],
                    'note' => $note['note'],
                    'saisie_par' => $_SESSION['user_id']
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur saisie des notes: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Publie les résultats d'un semestre
     * @param int $semestreId ID du semestre
     * @return bool True si la publication a réussi, false sinon
     */
    public function publierResultats($semestreId) {
        try {
            // Vérifier que le semestre est clôturé
            $semestre = $this->db->fetch(
                "SELECT * FROM {$this->table_semestres} 
                 WHERE id = :id AND est_cloture = 1",
                ['id' => $semestreId]
            );
            
            if (!$semestre) {
                throw new Exception("Le semestre n'est pas clôturé ou n'existe pas");
            }
            
            // Marquer comme publiées les notes validées ou saisies pour le semestre
            $sql = "UPDATE {$this->table_notes} n
                    JOIN matieres m ON n.matiere_id = m.id
                    JOIN semestres s ON m.semestre_id = s.id
                    SET n.statut = 'publie',
                        n.date_publication = NOW()
                    WHERE s.id = :semestre_id
                      AND (n.statut = 'valide' OR n.statut = 'saisie')";
            
            $this->db->execute($sql, ['semestre_id' => $semestreId]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur publication des résultats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Modifie une note après publication (uniquement pour l'admin)
     * @param int $noteId ID de la note à modifier
     * @param float $nouvelleNote Nouvelle valeur de la note
     * @param string $motif Motif de la modification
     * @return bool True si la modification a réussi, false sinon
     */
    public function modifierNoteApresPublication($noteId, $nouvelleNote, $motif) {
        try {
            $this->db->beginTransaction();
            
            // 1. Récupérer l'ancienne valeur de la note
            $oldNote = $this->db->fetch(
                "SELECT * FROM {$this->table_notes} WHERE id = :id",
                ['id' => $noteId]
            );
            
            if (!$oldNote) {
                throw new Exception("Note non trouvée");
            }
            
            // 2. Enregistrer l'ancienne valeur dans l'historique
            $this->db->insert(
                "INSERT INTO historique_notes 
                (note_id, ancienne_valeur, nouvelle_valeur, modifie_par, date_modification, motif)
                VALUES 
                (:note_id, :ancienne_valeur, :nouvelle_valeur, :modifie_par, NOW(), :motif)",
                [
                    'note_id' => $noteId,
                    'ancienne_valeur' => $oldNote['note'],
                    'nouvelle_valeur' => $nouvelleNote,
                    'modifie_par' => $_SESSION['user_id'],
                    'motif' => $motif
                ]
            );
            
            // 3. Mettre à jour la note
            $this->db->execute(
                "UPDATE {$this->table_notes} 
                 SET note = :note, 
                     modifie_par = :modifie_par,
                     date_modification = NOW()
                 WHERE id = :id",
                [
                    'id' => $noteId,
                    'note' => $nouvelleNote,
                    'modifie_par' => $_SESSION['user_id']
                ]
            );
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur modification note après publication: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les statistiques générales du système
     * @return array Tableau des statistiques
     */
    public function getStatistiques() {
        $stats = [];
        
        // Nombre total d'étudiants
        $stats['total_etudiants'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table_etudiants}"
        );
        
        // Nombre total de professeurs
        $stats['total_professeurs'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table_professeurs}"
        );
        
        // Nombre total de classes
        $stats['total_classes'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table_classes}"
        );
        
        // Nombre de notes saisies ce mois-ci
        $stats['notes_ce_mois'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table_notes} 
             WHERE MONTH(date_saisie) = MONTH(CURRENT_DATE())
             AND YEAR(date_saisie) = YEAR(CURRENT_DATE())"
        );
        
        return $stats;
    }
    
    /**
     * Génère un rapport d'activité
     * @param string $type Type de rapport (mensuel, trimestriel, annuel)
     * @param array $options Options supplémentaires (date de début, date de fin, etc.)
     * @return array Données du rapport
     */
    public function genererRapport($type = 'mensuel', $options = []) {
        $rapport = [];
        
        // Implémentation de base - à adapter selon les besoins
        switch ($type) {
            case 'mensuel':
                $sql = "SELECT 
                            DATE_FORMAT(date_saisie, '%Y-%m') as mois,
                            COUNT(*) as nb_notes,
                            AVG(note) as moyenne_generale
                        FROM {$this->table_notes}
                        WHERE date_saisie >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                        GROUP BY DATE_FORMAT(date_saisie, '%Y-%m')
                        ORDER BY mois DESC";
                break;
                
            case 'trimestriel':
                $sql = "SELECT 
                            CONCAT(YEAR(date_saisie), '-T', QUARTER(date_saisie)) as trimestre,
                            COUNT(*) as nb_notes,
                            AVG(note) as moyenne_generale
                        FROM {$this->table_notes}
                        WHERE date_saisie >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
                        GROUP BY YEAR(date_saisie), QUARTER(date_saisie)
                        ORDER BY YEAR(date_saisie) DESC, QUARTER(date_saisie) DESC";
                break;
                
            case 'annuel':
            default:
                $sql = "SELECT 
                            YEAR(date_saisie) as annee,
                            COUNT(*) as nb_notes,
                            AVG(note) as moyenne_generale
                        FROM {$this->table_notes}
                        GROUP BY YEAR(date_saisie)
                        ORDER BY annee DESC";
                break;
        }
        
        $rapport['donnees'] = $this->db->fetchAll($sql);
        $rapport['type'] = $type;
        $rapport['date_generation'] = date('Y-m-d H:i:s');
        
        return $rapport;
    }
}
