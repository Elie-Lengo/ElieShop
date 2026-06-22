<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/customer_auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (!empty($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = "Session expirée, merci de réessayer.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM customers WHERE email = ?');
        $stmt->execute([$email]);
        $found = $stmt->fetch();

        if ($found && password_verify($password, $found['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['customer_id'] = $found['id'];
            $_SESSION['customer_name'] = $found['name'];
            header('Location: index.php');
            exit;
        }
        $error = "Email ou mot de passe incorrect.";
    }
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | ElieShop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Connexion</h1>
            <p class="auth-sub">Connecte-toi à ton compte ElieShop.</p>

            <?php if ($msg === 'logged_out'): ?>
                <div class="alert alert-success">Tu as été déconnecté.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input class="form-control" type="password" id="password" name="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-block">Se connecter</button>
                </div>
            </form>

            <p style="text-align:center; margin-top:1.2rem; font-size:0.9rem;">
                Pas encore de compte ? <a href="register.php">Créer un compte</a>
            </p>
            <a class="back-link" href="../index.php">← Retour à la boutique</a>
        </div>
    </div>
</body>

</html>