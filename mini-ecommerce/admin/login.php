<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$userCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($userCount === 0) {
    header('Location: setup.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = "Session expirée, merci de réessayer.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header('Location: index.php');
            exit;
        }
        $error = "Identifiant ou mot de passe incorrect.";
    }
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Connexion admin | ElieShop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Espace admin</h1>
            <p class="auth-sub">Connecte-toi pour gérer la boutique ElieShop.</p>

            <?php if ($msg === 'account_created'): ?>
                <div class="alert alert-success">Compte créé avec succès. Tu peux te connecter.</div>
            <?php elseif ($msg === 'logged_out'): ?>
                <div class="alert alert-success">Tu as été déconnecté.</div>
            <?php elseif ($msg === 'already_configured'): ?>
                <div class="alert alert-error">Un compte admin existe déjà. Connecte-toi ci-dessous.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input class="form-control" type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input class="form-control" type="password" id="password" name="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-block">Se connecter</button>
                </div>
            </form>

            <a class="back-link" href="../index.php">← Retour à la boutique</a>
        </div>
    </div>
</body>

</html>