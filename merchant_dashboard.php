<?php
session_start();

// Vérification de l'authentification et du type d'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Merchant') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=digital_flea_market", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_merchant = $_SESSION['user_id'];

    // Récupérer les produits du marchand connecté
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id_merchant = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$id_merchant]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour calculer les revenus du mois (statut "Confirmed" ou "Delivered")
    $revenueSql = "
        SELECT SUM(o.total) AS total_revenue
        FROM orders o
        JOIN users u ON u.id_user = o.id_user
        WHERE u.id_user = :merchant_id
        AND DATE_FORMAT(o.order_date, '%Y-%m') = :month
        AND o.status IN ('Confirmed', 'Delivered')
    ";
    $revenueStmt = $pdo->prepare($revenueSql);
    $revenueStmt->bindValue(':merchant_id', $id_merchant, PDO::PARAM_INT);
    $revenueStmt->bindValue(':month', date('Y-m'), PDO::PARAM_STR);  // Utilisation du mois actuel
    $revenueStmt->execute();
    $revenueData = $revenueStmt->fetch(PDO::FETCH_ASSOC);
    $totalRevenue = $revenueData['total_revenue'] ? $revenueData['total_revenue'] : 0;  // Si aucun revenu, la valeur est 0

    // Requête pour calculer les ventes du mois (statut "Confirmed" ou "Delivered")
    $salesSql = "
        SELECT SUM(o.quantity) AS total_quantity
        FROM orders o
        JOIN users u ON u.id_user = o.id_user
        WHERE u.id_user = :merchant_id
        AND DATE_FORMAT(o.order_date, '%Y-%m') = :month
        AND o.status IN ('Confirmed', 'Delivered')
    ";
    $salesStmt = $pdo->prepare($salesSql);
    $salesStmt->bindValue(':merchant_id', $id_merchant, PDO::PARAM_INT);
    $salesStmt->bindValue(':month', date('Y-m'), PDO::PARAM_STR);  // Utilisation du mois actuel
    $salesStmt->execute();
    $salesData = $salesStmt->fetch(PDO::FETCH_ASSOC);
    $totalSales = $salesData['total_quantity'] ? $salesData['total_quantity'] : 0;  // Si aucune vente, la quantité est 0

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Marchand</title>
    <style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* HEADER */
header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: #333;
    color: #fff;
    padding: 7px 0;
    text-align: center;
    z-index: 1000;
}

header nav {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 10px;
}

header a {
    color: white;
    text-decoration: none;
    font-size: 1rem;
}

header a:hover {
    color: #2980b9;
}

/* CONTAINER PRINCIPAL */
.dashboard {
    display: flex;
    padding-top: 60px;
    width: 100%;
    min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    top: 60px;
    left: 0;
    width: 250px;
    height: calc(100vh - 60px);
    background: #3498db;
    color: white;
    padding: 20px;
    overflow-y: auto;
}

.sidebar h2 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar li {
    margin-bottom: 1rem;
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

/* CONTENU PRINCIPAL */
.main-content {
    flex: 1;
    margin-left: 250px;
    padding: 20px;
    background: #f8f9fa;
    overflow-y: auto;
}

.main-content .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.logout-btn {
    background: #e74c3c;
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
}

.logout-btn:hover {
    background: #c0392b;
}

/* STATISTIQUES */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.stat-card h3 {
    color: #666;
    margin-bottom: 0.5rem;
}

.stat-card .number {
    font-size: 2rem;
    font-weight: bold;
    color: #2c3e50;
}

/* TABLEAU DES ARTICLES RÉCENTS */
.recent-items {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.recent-items table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.recent-items th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

.recent-items th {
    background: #2c3e50;
    color: white;
}

.recent-items img {
    max-width: 80px;
    border-radius: 5px;
}

/* CATEGORIES - PAS DE CHANGEMENT */
.categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
    justify-content: center;
}

.category-card {
    width: 180px;
    height: 50px; /* Hauteur fixe pour les cartes de catégories */
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
    cursor: pointer;
    transition: transform 0.3s;
    display: flex;
    flex-direction: column;
    justify-content: center; /* Centrer le contenu */
    align-items: center;
}

.category-card a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
}

.category-card:hover {
    transform: translateY(-5px);
}

/* BOUTON DE COMMANDE */
.order-btn {
    display: inline-block;
    background: #28a745;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.order-btn:hover {
    background: #218838;
}

/* PIED DE PAGE */
footer {
    text-align: center;
    padding: 15px;
    background: #333;
    color: white;
    margin-top: 20px;
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
        <div class="header">
            <h1>Tableau de bord Marchand</h1>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Articles en vente</h3>
                <div class="number"><?= count($products) ?></div>
            </div>
            <div class="stat-card">
                <h3>Ventes du mois</h3>
                <div class="number"><?= htmlspecialchars($totalSales) ?></div> <!-- Affichage des ventes du mois -->
            </div>
            <div class="stat-card">
                <h3>Revenus du mois</h3>
                <div class="number"><?= htmlspecialchars($totalRevenue) ?> FCFA</div> <!-- Affichage des revenus du mois -->
            </div>

        </div>

        <div class="recent-items">
            <h2>Mes Articles Récents</h2>
            <table>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Date</th>
                </tr>
                <?php if (empty($products)): ?>
                    <tr><td colspan="5">Aucun article trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($product['image']) ?>" width="80"></td>
                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                            <td><?= number_format($product['price'], 2) ?>FCFA</td>
                            <td><?= $product['available_quantity'] ?></td>
                            <td><?= $product['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
</footer>

</body>
</html>
