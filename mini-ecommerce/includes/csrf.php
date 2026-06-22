<?php
// Protection CSRF basique pour tous les formulaires POST du site.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Génère (ou réutilise) un jeton CSRF stocké en session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Affiche un champ caché <input> contenant le jeton CSRF, à insérer dans un <form>.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Vérifie le jeton CSRF envoyé en POST par rapport à celui de la session.
 */
function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return $token !== '' && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
