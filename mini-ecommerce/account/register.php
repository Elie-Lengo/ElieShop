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
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            $error = "Merci de remplir tous les champs obligatoires.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Adresse email invalide.";
        } elseif (strlen($password) < 6) {
            $error = "Le mot de passe doit contenir au moins 6 caractères.";
        } elseif ($password !== $confirm) {
            $error = "Les deux mots de passe ne correspondent pas.";
        } else {
            $check = $pdo->prepare('SELECT id FROM customers WHERE email = ?');
            $check->execute([$email]);

            if ($check->fetch()) {
                $error = "Un compte existe déjà avec cet email.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT); //mot de passe crypté de php5
                $stmt = $pdo->prepare('INSERT INTO customers (name, email, phone, address, password_hash) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $email, $phone, $address, $hash]);

                session_regenerate_id(true);
                $_SESSION['customer_id'] = (int)$pdo->lastInsertId();
                $_SESSION['customer_name'] = $name;

                header('Location: index.php?welcome=1');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte | ElieShop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Créer mon compte</h1>
            <p class="auth-sub">Rejoins ElieShop pour suivre tes commandes facilement.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="name">Nom complet</label>
                    <input class="form-control" type="text" id="name" name="name" required autofocus
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input class="form-control" type="tel" id="phone" name="phone"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <input class="form-control" type="text" id="address" name="address"
                        value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
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

            <p style="text-align:center; margin-top:1.2rem; font-size:0.9rem;">
                Déjà un compte ? <a href="login.php">Se connecter</a>
            </p>
            <a class="back-link" href="../index.php">← Retour à la boutique</a>
        </div>
    </div>
</body>

</html>