<?php
require_once __DIR__ . '/../includes/customer_auth.php';

// On ne détruit pas toute la session : ça préserve le panier en cours,
// qui n'a pas à disparaître juste parce que le client se déconnecte.
unset($_SESSION['customer_id'], $_SESSION['customer_name']);
session_regenerate_id(true);

header('Location: login.php?msg=logged_out');
exit;
