<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/customer_auth.php';
require_customer_login();

$customer = current_customer();

$stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
$stmt->execute([$customer['id']]);
$profile = $stmt->fetch();

$ordersStmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC');
$ordersStmt->execute([$customer['id']]);
$orders = $ordersStmt->fetchAll();

$itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$welcome = isset($_GET['welcome']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Mon compte | ElieShop</title>
    <link rel="icon" href="../assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <header class="header">
        <div class="logo"><a href="../index.php" class="logo-link">ElieShop</a></div>
        <nav class="nav">
            <a href="../index.php">Boutique</a>
            <a href="../cart.php">🛒 Mon panier</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <main class="container">
        <h1 class="page-title">Bonjour, <?= htmlspecialchars($profile['name']) ?> 👋</h1>

        <?php if ($welcome): ?>
            <div class="alert alert-success">Bienvenue sur ElieShop, ton compte a été créé avec succès !</div>
        <?php endif; ?>

        <div class="form-card" style="max-width:none; margin-bottom:2rem;">
            <h3 style="margin-bottom:1rem; color:#2c3e50;">Mes informations</h3>
            <p><strong>Email :</strong> <?= htmlspecialchars($profile['email']) ?></p>
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($profile['phone'] ?? '-') ?></p>
            <p><strong>Adresse :</strong> <?= htmlspecialchars($profile['address'] ?? '-') ?></p>
            <p style="color:#999; font-size:0.85rem; margin-top:0.5rem;">
                Membre depuis le <?= date('d/m/Y', strtotime($profile['created_at'])) ?>
            </p>
        </div>

        <h3 style="margin-bottom:1rem; color:#2c3e50;">Mes commandes</h3>

        <?php if (!$orders): ?>
            <p style="color:#999;">Tu n'as pas encore passé de commande. <a href="../index.php">Découvrir la boutique →</a></p>
        <?php endif; ?>

        <?php foreach ($orders as $order): ?>
            <?php
            $itemsStmt->execute([$order['id']]);
            $items = $itemsStmt->fetchAll();
            ?>
            <div class="form-card" style="max-width:none; margin-bottom:1.2rem;">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <strong>Commande #<?= (int)$order['id'] ?></strong>
                        <div style="color:#888; font-size:0.85rem;">
                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                            · Paiement : <?= htmlspecialchars($order['payment_method']) ?>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div class="price" style="margin:0;">$<?= number_format((float)$order['total'], 2) ?></div>
                        <?php if ($order['status'] === 'traitee'): ?>
                            <span class="badge badge-success">Traitée</span>
                        <?php else: ?>
                            <span class="badge badge-muted">En attente</span>
                        <?php endif; ?>
                    </div>
                </div>
                <ul style="padding-left:1.2rem; color:#555;">
                    <?php foreach ($items as $item): ?>
                        <li><?= htmlspecialchars($item['product_name']) ?> × <?= (int)$item['quantity'] ?>
                            — $<?= number_format((float)$item['unit_price'] * (int)$item['quantity'], 2) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </main>

    <?php $footerBase = '../';
    include __DIR__ . '/../includes/site_footer.php'; ?>
</body>

</html>