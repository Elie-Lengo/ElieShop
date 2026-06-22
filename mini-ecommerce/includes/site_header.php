<?php
require_once __DIR__ . '/customer_auth.php';
require_once __DIR__ . '/cart.php';

$__customer = current_customer();
$__cartCount = cart_count();
?>
<header class="header">
    <div class="logo"><a href="index.php" class="logo-link">ElieShop</a></div>

    <a class="cart-quicklink" href="cart.php" aria-label="Voir mon panier">
        🛒 Panier
        <?php if ($__cartCount > 0): ?>
            <span class="cart-badge"><?= $__cartCount ?></span>
        <?php endif; ?>
    </a>

    <nav class="nav">
        <a href="index.php">Boutique</a>
        <?php if ($__customer): ?>
            <a href="account/index.php">👤 <?= htmlspecialchars($__customer['name']) ?></a>
        <?php else: ?>
            <a href="account/login.php">Connexion</a>
        <?php endif; ?>
    </nav>
</header>