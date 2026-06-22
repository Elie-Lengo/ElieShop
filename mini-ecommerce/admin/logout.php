<?php
require_once __DIR__ . '/../includes/auth.php';

// On retire uniquement les clés admin, sans tout détruire : ça évite de
// vider le panier d'un client si jamais le même navigateur partage la session.
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
session_regenerate_id(true);

header('Location: login.php?msg=logged_out');
exit;
