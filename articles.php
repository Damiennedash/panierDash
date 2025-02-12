<?php
session_start();

// Vérification de l'authentification et du type d'utilisateur

require_once 'config.php';

// Récupérer la recherche (si elle existe)
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Récupérer tous les articles disponibles (avec recherche)
try {
    $query = "SELECT p.*, u.name as merchant_name 
              FROM products p 
              JOIN users u ON p.id_merchant = u.id_user 
              WHERE p.name LIKE :search 
              ORDER BY p.id_product DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':search' => "%$search%"]);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des produits : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Articles</title>
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
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #3498db;
            color: white;
            padding: 2rem;
        }
        .sidebar h2 {
            margin-bottom: 2rem;
            font-size: 1.5rem;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar li {
            margin-bottom: 1rem;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: #2980b9;
        }
        .main-content {
            flex: 1;
            padding: 2rem;
            background: #f8f9fa;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .search-bar {
            margin-bottom: 2rem;
        }
        .search-bar input {
            width: 80%;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .search-bar button {
            padding: 1rem 2rem;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background: #45a049;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .product-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .product-card h3 {
            margin-bottom: 0.5rem;
        }
        .product-card p {
            margin-bottom: 0.5rem;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Vide Grenier</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="about.php">À propos</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="dashboard">
        <div class="sidebar">
            <h2>Menu Étudiant</h2>
            <ul>
                <li><a href="student_dashboard.php">Accueil</a></li>
                <li><a href="articles.php">Articles</a></li>
                <li><a href="mes_favoris.php">Mes Favoris</a></li>
                <li><a href="mes_achats.php">Mes Achats</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="mon_profil.php">Mon Profil</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Articles disponibles</h1>
                <a href="logout.php" class="btn">Déconnexion</a>
            </div>

            <!-- Barre de recherche -->
            <div class="search-bar">
                <form method="GET" action="articles.php">
                    <input type="text" name="search" placeholder="Rechercher des articles..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Rechercher</button>
                </form>
            </div>

            <!-- Affichage des articles -->
            <div class="product-grid">
                <?php if(isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <p>Aucune image disponible</p>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p><strong>Prix :</strong> <?php echo htmlspecialchars($product['price']); ?> €</p>
                        <p><strong>Vendeur :</strong> <?php echo htmlspecialchars($product['merchant_name']); ?></p>
                        <p><strong>Quantité disponible :</strong> <?php echo htmlspecialchars($product['available_quantity']); ?></p>
                        <a href="order.php?id=<?php echo $product['id_product']; ?>" class="btn">Commander</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>