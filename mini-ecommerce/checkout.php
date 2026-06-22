<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/customer_auth.php';

$customer = current_customer();

function render_shell_open(string $title): void
{
?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex">
        <title><?= htmlspecialchars($title) ?> | ElieShop</title>
        <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php require __DIR__ . '/includes/site_header.php'; ?>
        <main class="container">
        <?php
    }

    function render_shell_close(): void
    {
        ?>
        </main>
        <?php include __DIR__ . '/includes/site_footer.php'; ?>
    </body>

    </html>
<?php
    }

    // ============================================
    // TRAITEMENT DE LA COMMANDE (POST)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!csrf_verify()) {
            render_shell_open('Erreur');
            echo '<div class="error-page"><h1>Oups</h1><p>Ta session a expiré, merci de réessayer.</p>
              <a class="btn" href="cart.php" style="display:inline-block;width:auto;">Retour au panier</a></div>';
            render_shell_close();
            exit;
        }

        // On revalide le panier au moment du paiement (stock à jour)
        $items = cart_items($pdo);
        if (!$items) {
            header('Location: cart.php');
            exit;
        }

        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $customerAddress = trim($_POST['customer_address'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? '');

        if ($customerName === '' || $customerPhone === '' || $paymentMethod === '') {
            render_shell_open('Commande incomplète');
            echo '<div class="error-page"><h1>Oups</h1><p>Merci de remplir tous les champs obligatoires.</p>
              <a class="btn" href="checkout.php" style="display:inline-block;width:auto;">Réessayer</a></div>';
            render_shell_close();
            exit;
        }

        $total = 0.0;
        foreach ($items as $it) {
            $total += $it['subtotal'];
        }

        try {
            $pdo->beginTransaction();

            $insertOrder = $pdo->prepare(
                'INSERT INTO orders (customer_id, customer_name, customer_phone, customer_address, payment_method, total, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $insertOrder->execute([
                $customer['id'] ?? null,
                $customerName,
                $customerPhone,
                $customerAddress,
                $paymentMethod,
                $total,
                'en_attente'
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $insertItem = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)'
            );
            $updateStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

            foreach ($items as $it) {
                $p = $it['product'];
                $qty = $it['quantity'];
                $insertItem->execute([$orderId, $p['id'], $p['name'], $p['price'], $qty]);
                $updateStock->execute([$qty, $p['id'], $qty]);
            }

            $pdo->commit();
            cart_clear();
        } catch (Exception $e) {
            $pdo->rollBack();
            render_shell_open('Erreur');
            echo '<div class="error-page"><h1>Oups</h1><p>Une erreur est survenue lors de l\'enregistrement de ta commande.</p>
              <a class="btn" href="cart.php" style="display:inline-block;width:auto;">Retour au panier</a></div>';
            render_shell_close();
            exit;
        }

        render_shell_open('Commande confirmée');
?>
    <div class="order-success">
        <div class="check">✅</div>
        <h1>Merci, <?= htmlspecialchars($customerName) ?> !</h1>
        <p>Ta commande <span class="order-ref">#<?= $orderId ?></span> a bien été enregistrée.</p>
        <p>Total : <strong>$<?= number_format($total, 2) ?></strong> — Paiement : <?= htmlspecialchars($paymentMethod) ?></p>
        <p style="color:#888; font-size:0.9rem; margin-top:1rem;">
            Tu seras contacté(e) au <?= htmlspecialchars($customerPhone) ?> pour finaliser la livraison.
        </p>
        <?php if ($customer): ?>
            <a class="btn btn-outline" href="account/index.php" style="display:inline-block; width:auto; margin-top:1rem; margin-right:0.8rem;">Voir mes commandes</a>
        <?php endif; ?>
        <a class="btn" href="index.php" style="display:inline-block; width:auto; margin-top:1rem;">Continuer mes achats</a>
    </div>
<?php
        render_shell_close();
        exit;
    }

    // ============================================
    // AFFICHAGE DU RÉCAPITULATIF + FORMULAIRE (GET)
    // ============================================
    $items = cart_items($pdo);
    if (!$items) {
        header('Location: cart.php');
        exit;
    }

    $total = 0.0;
    foreach ($items as $it) {
        $total += $it['subtotal'];
    }

    $prefillName = '';
    $prefillPhone = '';
    $prefillAddress = '';

    if ($customer) {
        $stmt = $pdo->prepare('SELECT name, phone, address FROM customers WHERE id = ?');
        $stmt->execute([$customer['id']]);
        if ($row = $stmt->fetch()) {
            $prefillName = $row['name'] ?? '';
            $prefillPhone = $row['phone'] ?? '';
            $prefillAddress = $row['address'] ?? '';
        }
    }

    render_shell_open('Finaliser ma commande');
?>
<h1 class="page-title">Finaliser ma commande</h1>

<div style="display:flex; gap:2rem; flex-wrap:wrap; align-items:flex-start;">
    <div style="flex:1 1 280px;">
        <h3 style="margin-bottom:1rem; color:#2c3e50;">Récapitulatif</h3>
        <?php foreach ($items as $it): $p = $it['product']; ?>
            <div style="display:flex; justify-content:space-between; padding:0.6rem 0; border-bottom:1px solid #eee;">
                <span><?= htmlspecialchars($p['name']) ?> × <?= $it['quantity'] ?></span>
                <strong>$<?= number_format($it['subtotal'], 2) ?></strong>
            </div>
        <?php endforeach; ?>
        <div style="display:flex; justify-content:space-between; padding-top:1rem; font-size:1.2rem;">
            <strong>Total</strong><strong>$<?= number_format($total, 2) ?></strong>
        </div>
    </div>

    <div class="form-card" style="flex:1 1 320px; margin:0;">
        <form method="POST" id="checkoutForm">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="customer_name">Nom complet</label>
                <input class="form-control" type="text" id="customer_name" name="customer_name" required
                    value="<?= htmlspecialchars($prefillName) ?>">
            </div>
            <div class="form-group">
                <label for="customer_phone">Téléphone</label>
                <input class="form-control" type="tel" id="customer_phone" name="customer_phone" required
                    value="<?= htmlspecialchars($prefillPhone) ?>">
            </div>
            <div class="form-group">
                <label for="customer_address">Adresse de livraison</label>
                <input class="form-control" type="text" id="customer_address" name="customer_address"
                    value="<?= htmlspecialchars($prefillAddress) ?>">
            </div>
            <div class="form-group">
                <label>Mode de paiement</label>
                <div class="pay-options">
                    <label class="pay-option selected">
                        <input type="radio" name="payment_method" value="Mobile Money" checked>
                        📱 Mobile Money
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="Carte bancaire">
                        💳 Carte
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-block">Confirmer la commande ($<?= number_format($total, 2) ?>)</button>
            </div>
        </form>

        <?php if (!$customer): ?>
            <p style="text-align:center; margin-top:1rem; font-size:0.85rem; color:#888;">
                <a href="account/login.php">Se connecter</a> pour retrouver tes commandes plus facilement.
            </p>
        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.pay-option input').forEach(input => {
        input.addEventListener('change', () => {
            document.querySelectorAll('.pay-option').forEach(o => o.classList.remove('selected'));
            input.closest('.pay-option').classList.add('selected');
        });
    });
</script>
<?php
render_shell_close();
