<?php
// Gestion de la session admin. À inclure dans chaque page protégée.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Bloque l'accès à la page si aucun admin n'est connecté.
 * Redirige vers la page de connexion.
 */
function require_login(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Retourne le nom d'utilisateur de l'admin actuellement connecté, ou null.
 */
function current_admin_username(): ?string
{
    return $_SESSION['admin_username'] ?? null;
}
