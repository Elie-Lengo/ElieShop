<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

$userCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($userCount > 0) {
    header('Location: login.php?msg=already_configured');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = "Session expirée, merci de réessayer.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if ($username === '' || $password === '') {
            $error = "Tous les champs sont obligatoires.";
        } elseif (strlen($password) < 6) {
            $error = "Le mot de passe doit contenir au moins 6 caractères.";
        } elseif ($password !== $confirm) {
            $error = "Les deux mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, $hash]);
            header('Location: login.php?msg=account_created');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Créer le compte admin | ElieShop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Bienvenue 👋</h1>
            <p class="auth-sub">Crée le premier compte administrateur de ElieShop.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input class="form-control" type="text" id="username" name="username" required autofocus
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input class="form-control" type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm">Confirmer le mot de passe</label>
                    <input class="form-control" type="password" id="confirm" name="confirm" required minlength="6">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-block">Créer mon compte</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>