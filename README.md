Portail Universitaire – HEC Abidjan
1. Présentation

Le Portail Universitaire de HEC Abidjan est un système web de gestion académique conçu pour centraliser et sécuriser toutes les informations relatives aux années universitaires, classes, étudiants, professeurs et résultats scolaires.

Il permet de :

Gérer les années universitaires et semestres

Gérer les classes et leurs effectifs

Gérer les professeurs et leurs matières

Gérer les étudiants et leurs résultats

Faciliter le suivi académique et l’accès aux bulletins

2. Rôles et accès
Rôle	Accès / Actions principales
Admin	- Création des années, semestres et classes
- Création des comptes chef de classe
- Saisie et validation des notes
- Publication des résultats
- Consultation complète de l’historique
Chef de classe	- Saisie des listes d’étudiants (matricule, nom, prénom, date et lieu de naissance)
- Saisie des listes de professeurs et leurs matières
- Modification et suppression avant soumission à l’Admin
Professeur	- Saisie des notes et moyennes des étudiants pour les matières qui lui sont attribuées
- Consultation des listes de ses classes et matières
Étudiant	- Consultation et impression des notes et bulletins
- Accès à l’historique de ses résultats
3. Fonctionnement des comptes

Les comptes étudiants et professeurs sont créés automatiquement à partir des listes soumises par les chefs de classe.

Les comptes chefs de classe sont créés manuellement par l’Admin chaque année universitaire.

Tous les utilisateurs doivent changer leur mot de passe lors de la première connexion.

Une fois le mot de passe changé, ils peuvent se reconnecter chaque année avec les mêmes identifiants.

4. Architecture du projet
hec-portal/
│
├── index.php              # Page d’accueil
├── config/                # Configurations (base de données, constants)
├── includes/              # Fichiers réutilisables (header, footer, fonctions)
├── assets/                # CSS, JS, images
├── pages/                 # Pages web par rôle (admin, chef_classe, professeur, etudiant)
├── classes/               # Classes PHP de la logique métier
├── controllers/           # Scripts de traitement (formulaires, actions)
├── database/              # Scripts SQL (tables, seed)
├── logs/                  # Journaux et erreurs
└── .htaccess              # Sécurisation des URLs

5. Fonctionnalités principales

Gestion des années et semestres

Création, ouverture et fermeture par l’Admin

Historique complet des années universitaires

Gestion des classes

Création et suppression des classes par l’Admin

Affectation des étudiants et professeurs via les listes du chef de classe

Gestion des comptes utilisateurs

Création automatique des comptes étudiants et professeurs

Création manuelle des comptes chefs de classe par l’Admin

Gestion des matières et affectations

Définition des matières par classe par l’Admin

Affectation des matières aux professeurs via combobox par le chef de classe

Un professeur peut enseigner plusieurs matières, mais une matière ne peut être enseignée que par un seul professeur par classe

Gestion des notes et résultats

Saisie des notes par classe (Admin et Professeurs)

Validation par l’Admin

Publication après fermeture du semestre

Consultation et impression des bulletins

6. Technologies utilisées

Front-End : HTML5, CSS3 (Bootstrap), JavaScript

Back-End : PHP pur

Base de données : MySQL