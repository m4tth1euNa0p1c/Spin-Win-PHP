## README - Projet Web "Spin & Win"

Bienvenue dans notre **projet web de casino en ligne** nommé **"Spin & Win"**, qui comprend :

1. **Authentification & Gestion Utilisateur**  
   - Inscription, connexion, déconnexion  
   - Gestion sécurisée des mots de passe (hash, récupération)  
   - Sessions utilisateur (CSRF tokens, etc.)

2. **Machines à Sous**  
   - **Slot Machine classique** : rouleaux, fruits, gains  
   - **Ultra Gains** : slot machine à 9 cases avec symboles précieux et gains plus élevés  
   - Calcul des gains par combinaisons (lignes, colonnes, diagonales)  
   - Gestion du solde (coins) : mises, gains, transactions

3. **Promotions & Pages Front**  
   - Pages statiques (Home, About, Contact, Promotions)  
   - Promotions avec animations et style casino  
   - Formulaire de contact

4. **Technologies & Structure**  
   - **PHP 7+ / 8+** (MVC artisanal)  
   - **Twig** pour le rendu de templates  
   - **MySQL** (via PDO) pour la gestion des données  
   - **Docker** pour l’environnement (containers PHP/Apache/MySQL)  
   - **Sass/CSS** et **JavaScript** (animations, AJAX)  
   - **Routes** et **Contrôleurs** modulaires (`Router.php`, `Route.php`)  
   - **Services** pour la logique métier (Ex. AuthService)

5. **Comment démarrer ?**  
   1. **Cloner le repo** : `git clone ...`  
   2. **Installer les dépendances** (Composer) : `composer install`  
   3. **Démarrer Docker** (si vous utilisez Docker) : `docker-compose up -d`  
   4. **Initialiser la base** (exécuter migrations ou scripts .sql)  
   5. **Accéder au site** : `http://localhost:8000`

6. **Fonctionnalités Clés**  
   - Sécurité : CSRF tokens, sessions, validations  
   - Calcul de gains : symboles, mapping, bonus, jackpot  
   - Transactions (coins) : historique entrées/sorties (withdrawal, win, etc.)  
   - Back-end MVC : Contrôleurs + Modèles + Vue Twig  
   - Front-end : CSS responsive, animations JS