<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

// On n'accepte plus la suppression par simple lien GET : un robot ou un clic
// accidentel pourrait sinon déclencher une suppression involontaire.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

$stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($product) {
    if (!empty($product['image']) && file_exists(__DIR__ . '/../assets/uploads/' . $product['image'])) {
        unlink(__DIR__ . '/../assets/uploads/' . $product['image']);
    }
    $delete = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $delete->execute([$id]);
}

header('Location: index.php?success=1');
exit;
