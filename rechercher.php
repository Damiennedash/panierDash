<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

require_once 'config.php';  // Assure-toi que config.php définit $pdo correctement

// Récupérer tous les articles disponibles
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.id_category = c.id_category 
        WHERE p.available_quantity > 0";

$stmt = $pdo->query($sql); // Utiliser $pdo au lieu de $conn
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rechercher des Articles</title>
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
            padding: 10px 0;
            text-align: center;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
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
            margin-bottom: 20px;
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
        .product-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .product-card h3 {
            margin-top: 0;
        }
        .product-card p {
            margin: 10px 0;
        }
        .product-card button {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .product-card button:hover {
            background: #218838;
        }
        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Vide Grenier</h1>
        <nav>
            <a href="../index.php">Accueil</a>
            <a href="../about.php">À propos</a>
            <a href="../logout.php">Déconnexion</a>
        </nav>
    </header>

    <div class="dashboard">
        <div class="sidebar">
            <h2>Menu Étudiant</h2>
            <ul>
                <li><a href="student_dashboard.php">Accueil</a></li>
                <li><a href="rechercher.php">Rechercher</a></li>
                <li><a href="mes_favoris.php">Mes Favoris</a></li>
                <li><a href="mes_achats.php">Mes Achats</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="mon_profil.php">Mon Profil</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Rechercher des Articles</h1>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <p>Prix: <?= htmlspecialchars($product['price']) ?>€</p>
                    <p>Quantité disponible: <?= htmlspecialchars($product['available_quantity']) ?></p>
                    <p>Catégorie: <?= htmlspecialchars($product['category_name']) ?></p>
                    <a href="commander.php?id=<?= $product['id_product'] ?>">
                        <button>Commander</button>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>
