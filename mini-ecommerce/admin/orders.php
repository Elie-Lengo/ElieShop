<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

// Changement de statut d'une commande (ex: marquer comme traitée)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    if (csrf_verify()) {
        $orderId = (int)$_POST['order_id'];
        $status = $_POST['status'] === 'traitee' ? 'traitee' : 'en_attente';
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
    }
    header('Location: orders.php');
    exit;
}

$orders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();

$itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Commandes | ElieShop Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <header class="admin-topbar">
        <div class="brand">ElieShop <span>Admin</span></div>
        <nav>
            <a href="../index.php">Voir la boutique</a>
            <a href="index.php">Produits</a>
            <a href="orders.php">Commandes</a>
            <span class="admin-user">👤 <?= htmlspecialchars(current_admin_username() ?? '') ?></span>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <main class="container">
        <h1 class="page-title">Commandes</h1>
        <p class="page-subtitle"><?= count($orders) ?> commande(s) reçue(s).</p>

        <?php if (!$orders): ?>
            <div class="table-responsive" style="padding:2rem; text-align:center; color:#999;">
                Aucune commande pour le moment.
            </div>
        <?php endif; ?>

        <?php foreach ($orders as $order): ?>
            <?php
            $itemsStmt->execute([$order['id']]);
            $items = $itemsStmt->fetchAll();
            ?>
            <div class="form-card" style="max-width:none; margin-bottom:1.5rem;">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <strong>Commande #<?= (int)$order['id'] ?></strong>
                        — <?= htmlspecialchars($order['customer_name']) ?>
                        (<?= htmlspecialchars($order['customer_phone']) ?>)
                        <div style="color:#888; font-size:0.85rem;">
                            <?= htmlspecialchars($order['customer_address'] ?? '') ?>
                            · <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
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

                <table class="admin-table" style="margin-bottom:1rem;">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix unit.</th>
                            <th>Qté</th>
                            <th>Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td>$<?= number_format((float)$item['unit_price'], 2) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td>$<?= number_format((float)$item['unit_price'] * (int)$item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                    <input type="hidden" name="status" value="<?= $order['status'] === 'traitee' ? 'en_attente' : 'traitee' ?>">
                    <button type="submit" class="btn btn-sm btn-outline">
                        <?= $order['status'] === 'traitee' ? 'Remettre en attente' : 'Marquer comme traitée' ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </main>

</body>

</html>