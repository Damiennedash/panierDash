<?php
session_start();
require_once 'config.php'; // Connexion à la base de données avec PDO

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Veuillez vous connecter.");
}

$userId = $_SESSION['user_id'];

// 🛒 Récupérer les commandes en attente ("In Progress") 
$orderSql = "
    SELECT o.id_order, o.id_product, o.order_date, 
           p.product_name, p.price, u.name AS merchant_name, o.quantity, 
           u.id_user AS merchant_id, o.status
    FROM orders o
    JOIN products p ON o.id_product = p.id_product
    JOIN users u ON p.id_merchant = u.id_user
    WHERE o.id_student = :user_id 
    AND (o.status = 'confirmed' OR o.status = 'delivered')
    ORDER BY u.name, o.order_date DESC
";
$orderStmt = $pdo->prepare($orderSql);
$orderStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

try {
    $orderStmt->execute();
    $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des commandes : " . $e->getMessage());
}

// 📌 Organiser les commandes par marchand et calculer le total par marchand
$merchantOrders = [];
$merchantTotals = [];

foreach ($orders as $order) {
    $merchantName = $order['merchant_name'];
    $merchantOrders[$merchantName][] = $order;

    if (!isset($merchantTotals[$merchantName])) {
        $merchantTotals[$merchantName] = 0;
    }
    $merchantTotals[$merchantName] += $order['price'] * $order['quantity'];
}

// 🗑️ Suppression d'une commande
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order_id'])) {
    $deleteOrderId = $_POST['delete_order_id'];

    $deleteSql = "DELETE FROM orders WHERE id_order = :id_order AND id_student = :id_student";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->bindValue(':id_order', $deleteOrderId, PDO::PARAM_INT);
    $deleteStmt->bindValue(':id_student', $userId, PDO::PARAM_INT);

    try {
        $deleteStmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erreur lors de la suppression de la commande : " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Achats</title>
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 0; background-color: #fafafa; }
        header { background-color: #3498db; color: white; padding: 20px 0; text-align: center; font-size: 24px; }

        .dashboard { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #2980b9;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 2px 0px 10px rgba(0,0,0,0.1);
        }
        .sidebar h2 { font-size: 22px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { margin-bottom: 15px; }
        .sidebar a { color: white; text-decoration: none; padding: 12px; display: block; border-radius: 8px; font-size: 16px; transition: background 0.3s; }
        .sidebar a:hover { background-color: #1f6fb2; }

        /* Main content */
        .main-content {
            margin-left: 270px;
            padding: 30px;
            background-color: #f8f9fa;
        }

        .merchant-section {
            background-color: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
        }
        .merchant-section h3 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #2c3e50;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-size: 16px;
        }
        th {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <header>Mes Achats</header>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Menu Étudiant</h2>
            <ul>
                <li><a href="student_dashboard.php">Accueil</a></li>
                <li><a href="mes_favoris.php">Mes Favoris</a></li>
                <li><a href="mes_achats.php">Mes Achats</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="mon_profil.php">Mon Profil</a></li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <h2>Historique des Commandes</h2>

            <?php foreach ($merchantOrders as $merchant => $orders): ?>
                <div class="merchant-section">
                    <h3>Marchand : <?= htmlspecialchars($merchant) ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Commande</th>
                                <th>Produit</th>
                                <th>Prix</th>
                                <th>Quantité</th>
                                <th>Total</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id_order'] ?></td>
                                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                                    <td><?= number_format($order['price'], 2) ?> FCFA</td>
                                    <td><?= $order['quantity'] ?></td>
                                    <td><?= number_format($order['price'] * $order['quantity'], 2) ?> FCFA</td>
                                    <td>
                                        <!-- Vérification de l'existence de la clé 'status' -->
                                        <span><?= isset($order['status']) ? ucfirst($order['status']) : 'Statut non défini' ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
