<?php
session_start();
require_once 'config.php'; // Connexion PDO

// Récupérer l'ID du marchand connecté
$merchantId = $_SESSION['user_id'];

// Récupérer les commandes avec pagination et filtre
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$orderSql = "
    SELECT o.id_order, o.order_date, o.status, o.quantity, o.numero, o.total,
           p.product_name, p.price, u.name AS student_name
    FROM orders o
    JOIN products p ON o.id_product = p.id_product
    JOIN users u ON o.id_student = u.id_user
    WHERE p.id_merchant = :merchant_id
    " . ($statusFilter ? " AND o.status = :status" : "") . "
    ORDER BY o.order_date DESC
    LIMIT :limit OFFSET :offset
";
$orderStmt = $pdo->prepare($orderSql);
$orderStmt->bindValue(':merchant_id', $merchantId, PDO::PARAM_INT);
$orderStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$orderStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($statusFilter) {
    $orderStmt->bindValue(':status', $statusFilter, PDO::PARAM_STR);
}
$orderStmt->execute();
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la modification du statut
if (isset($_POST['update_status']) && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['update_status'];

    $updateSql = "UPDATE orders SET status = :status WHERE id_order = :order_id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->bindValue(':status', $newStatus, PDO::PARAM_STR);
    $updateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
    $updateStmt->execute();

    header("Location: commandes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #3498db;
            color: white;
            padding: 20px;
        }
        .sidebar h2 {
            font-size: 1.5rem;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin-bottom: 10px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: #2980b9;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #3498db;
            color: white;
        }
        .order-status {
            font-weight: bold;
        }
        .order-status.delivered {
            color: #4caf50;
        }
        .order-status.cancelled {
            color: #f44336;
        }
        .status-buttons button {
            padding: 8px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin: 2px;
            color: white;
        }
        .status-buttons .delivered {
            background-color: #4caf50;
        }
        .status-buttons .cancelled {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <header>
        <h1>Gestion des Commandes</h1>
    </header>

    <div class="dashboard">
        <div class="sidebar">
            <h2>Menu Marchand</h2>
            <ul>
                <li><a href="merchant_dashboard.php">Tableau de bord</a></li>
                <li><a href="mes_articles.php">Mes Articles</a></li>
                <li><a href="ajouter_article.php">Ajouter un Article</a></li>
                <li><a href="commandes.php">Commandes</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="statistiques.php">Statistiques</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h2>Liste des Commandes</h2>

            <table>
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Date</th>
                        <th>Étudiant</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Numero</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['id_order']) ?></td>
                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                            <td><?= htmlspecialchars($order['price']) ?> FCFA</td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td><?= htmlspecialchars($order['student_name']) ?></td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                            <td><?= htmlspecialchars($order['total']) ?></td>
                            <td><?= htmlspecialchars($order['numero']) ?></td>
                            <td class="order-status <?= strtolower($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </td>
                            <td class="status-buttons">
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id_order']) ?>">
                                    <button type="submit" name="update_status" value="Delivered" class="delivered">Livrée</button>
                                    <button type="submit" name="update_status" value="Cancelled" class="cancelled">Annulée</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
