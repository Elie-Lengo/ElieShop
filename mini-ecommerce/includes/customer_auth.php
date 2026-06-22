<?php
// Gestion de la session client (espace "Mon compte"), totalement séparée
// de la session admin (includes/auth.php).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Bloque l'accès à la page si aucun client n'est connecté.
 */
function require_customer_login(): void
{
    if (empty($_SESSION['customer_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Retourne les infos du client connecté, ou null si personne n'est connecté.
 */
function current_customer(): ?array
{
    if (empty($_SESSION['customer_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['customer_id'],
        'name' => $_SESSION['customer_name'] ?? '',
    ];
}
