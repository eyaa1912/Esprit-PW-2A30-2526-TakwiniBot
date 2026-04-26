<?php
session_start();

// DB connection (same as yours)
$pdo = new PDO("mysql:host=localhost;dbname=takwini_db;charset=utf8mb4", "root", "");

// Remove item
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: cart.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon Panier</title>
</head>
<body>

<h2>🛒 Mon Panier</h2>

<?php if (empty($cart)): ?>
    <p>Votre panier est vide</p>
<?php else: ?>

<table border="1" cellpadding="10">
    <tr>
        <th>Produit</th>
        <th>Prix</th>
        <th>Quantité</th>
        <th>Total</th>
        <th>Action</th>
    </tr>

<?php foreach ($cart as $id => $qty): ?>

<?php
$stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
$subtotal = $product['prix'] * $qty;
$total += $subtotal;
?>

<tr>
    <td><?= $product['nom'] ?></td>
    <td><?= $product['prix'] ?> €</td>
    <td><?= $qty ?></td>
    <td><?= $subtotal ?> €</td>
    <td>
        <a href="cart.php?remove=<?= $id ?>">❌</a>
    </td>
</tr>

<?php endforeach; ?>

</table>

<h3>Total: <?= $total ?> €</h3>

<?php endif; ?>

<br>
<a href="gestion-produits.php">⬅ Continue shopping</a>

</body>
</html>