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
       body {
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
    background: url('background.jpg') no-repeat center center/cover;
    background-color: rgba(0, 0, 0, 0.5); /* Transparence */
    color: #fff;
    overflow-x: hidden;
    min-height: 100vh; /* Assure que le body prend toute la hauteur de l'écran */
    margin: 0;
}

/* HEADER */
header {
    background: linear-gradient(90deg, #FFFFFF, #467FD1, #24416B); /* Dégradé */
    color: #fff;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(10px); /* Effet de flou */
}

header .logo {
    height: 50px;
}

header nav {
    display: flex;
    gap: 15px;
}

header nav a {
    color: #fff;
    text-decoration: none;
    font-size: 1rem;
    transition: color 0.3s, transform 0.3s;
    }
 header nav a:hover {
    color: #24416B; /* Bleu */
    transform: translateY(-2px);
}

/* Dashboard */
.dashboard {
    display: flex;
    margin-top: 70px; /* Crée un espace pour le header fixe */
    height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background-color: #3498db;
    color: white;
    padding: 20px;
    position: fixed;
    top: 70px; /* Positionne le sidebar sous le header */
    left: 0;
    bottom: 0;
    box-shadow: 2px 0px 10px rgba(0,0,0,0.1);
    z-index: 999;
}

.sidebar h2 {
    font-size: 22px;
    margin-bottom: 20px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar li {
    margin-bottom: 15px;
}

.sidebar a {
    color: white;
    text-decoration: none;
    padding: 12px;
    display: block;
    border-radius: 8px;
    font-size: 16px;
    transition: background 0.3s;
}

.sidebar a:hover {
    background-color: #1f6fb2;
}

/* Main content */
.main-content {
    margin-left: 270px; /* Espace pour le sidebar */
    padding: 30px;
    background-color: #f8f9fa;
    color: #000; /* Texte en noir */
    flex: 1;
    margin-top: 20px; /* Création d'un espace sous le header */
}

/* Section Marchand */
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

/* Footer */
footer {
    background-color: #24416B;
    color: white;
    padding: 10px 20px;
    text-align: center;
    position: fixed;
    bottom: 0;
    width: 100%;
}

footer p {
    margin: 0;
    font-size: 14px;
}

    </style>
</head>
<body>
    <header>
     <img src="logo_Dash.png" alt="Logo" class="logo">             Mes Achats
    </header>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Menu Étudiant</h2>
            <ul>
            <li><a href="student_dashboard.php">Accueil</a></li>
                <li><a href="suivi_commande.php">Mes commandes validés</a></li>
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
