ElieShop — MINI PROJET E-COMMERCE PHP + MYSQL
================================================

ARCHITECTURE (3 espaces bien séparés)
- Espace CLIENT (public)  : index.php, product.php, cart.php, checkout.php, account/
- Espace ADMIN (privé)    : admin/  (jamais lié depuis l'espace client, comme sur Amazon)
- Moteur partagé          : includes/ (panier, sessions, sécurité CSRF)

INSTALLATION (nouvelle installation, base de données vide)
1. Copie le dossier mini-ecommerce dans htdocs (XAMPP), www ou public_html.
2. Va dans phpMyAdmin et importe le fichier database.sql.
3. Ouvre config/db.php et adapte si besoin ($host, $dbname, $username, $password).
4. Lance le site : http://localhost/.../mini-ecommerce/index.php
5. Va sur /admin/setup.php pour créer ton premier compte ADMIN
   (cette page se désactive automatiquement une fois le compte créé).
6. Connecte-toi sur /admin/login.php pour gérer les produits et commandes.

SI TU AS DÉJÀ UNE BASE EXISTANTE
Importe migration.sql dans phpMyAdmin : il ajoute les nouvelles tables/colonnes
(stock, users, customers, orders.customer_id, order_items) sans toucher à tes
produits existants.

PAGES CLIENT (publiques)
- Boutique                    : index.php
- Détails produit              : product.php?id=1
- Ajouter au panier             : bouton sous chaque produit (index.php / product.php)
- Mon panier                    : cart.php
- Finaliser la commande         : checkout.php
- Créer un compte client        : account/register.php
- Connexion client               : account/login.php
- Mon compte (profil + commandes) : account/index.php
- Déconnexion client              : account/logout.php

PAGES ADMIN (connexion admin obligatoire, séparée des clients)
- Création du 1er compte (une seule fois) : admin/setup.php
- Connexion admin                          : admin/login.php
- Déconnexion admin                        : admin/logout.php
- Liste des produits                       : admin/index.php
- Ajouter un produit                       : admin/add_product.php
- Modifier un produit                      : admin/edit_product.php?id=1
- Supprimer un produit                     : formulaire sécurisé depuis admin/index.php
- Commandes reçues                          : admin/orders.php

LOGIQUE CLIENT / ADMIN (important)
- Le client ne voit JAMAIS de lien vers /admin nulle part sur le site public
  (ni dans le header, ni dans le footer) — exactement comme sur Amazon/Alibaba.
  L'admin accède à son espace en tapant directement l'URL /admin/login.php.
- Les deux espaces utilisent des sessions séparées :
  $_SESSION['customer_id'] pour les clients, $_SESSION['admin_id'] pour les admins.
  Se déconnecter d'un espace ne déconnecte jamais l'autre, et ne vide pas le panier.
- Le panier ($_SESSION['cart']) est indépendant des deux : un visiteur peut
  ajouter des produits au panier sans être connecté, puis se connecter (ou
  rester invité) au moment de payer.

FONCTIONNALITÉS DE CETTE VERSION
- CSS d'origine uniquement (Tailwind retiré, fonctionne hors-ligne).
- Authentification admin ET client (sessions PHP séparées).
- Protection CSRF sur tous les formulaires.
- Suppression de produit via formulaire POST protégé (plus de lien GET).
- Validation réelle du type MIME des images uploadées.
- Gestion du stock par produit (décrémenté à l'achat, jamais de survente).
- Panier multi-produits avec icône + compteur visible en permanence.
- Vraie commande enregistrée en base (orders / order_items), liée au compte
  client si connecté, ou en "invité" sinon.
- Historique des commandes consultable dans "Mon compte".
- Page admin "Commandes" pour suivre et marquer les commandes traitées.
- Footer, favicon, meta description, page 404 stylée, site responsive.

SÉCURITÉ — À FAIRE AVANT DE METTRE EN LIGNE PUBLIQUEMENT
- Change les identifiants de ta base MySQL en production (jamais root sans
  mot de passe hors environnement local).
- Active HTTPS en production.
- Supprime admin/setup.php une fois ton compte admin créé et le site en ligne.

PISTES D'AMÉLIORATION FUTURES (si tu veux aller plus loin)
- Modification du profil client (actuellement lecture seule sur account/index.php).
- Pagination de la liste des produits si le catalogue grandit beaucoup.
- Vraie intégration de paiement (Mobile Money / carte) au lieu de la simple
  sélection du mode de paiement.
