<?php
session_start();

// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=digital_flea_market', 'root', '');

// Fonction pour récupérer les ventes par période
function getSalesByPeriod($pdo, $merchant_id, $period) {
    $sql = "";
    switch ($period) {
        case 'day':
            $sql = "SELECT SUM(o.total) as total, DATE(o.order_date) as date 
                    FROM orders o 
                    WHERE o.id_user = ? AND o.order_date >= CURDATE() 
                    AND o.status IN ('delivered', 'confirmed') 
                    GROUP BY DATE(o.order_date)";
            break;
        case 'week':
            $sql = "SELECT SUM(o.total) as total, WEEK(o.order_date) as week 
                    FROM orders o 
                    WHERE o.id_user = ? AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) 
                    AND o.status IN ('delivered', 'confirmed') 
                    GROUP BY WEEK(o.order_date)";
            break;
        case 'month':
            $sql = "SELECT SUM(o.total) as total, MONTH(o.order_date) as month 
                    FROM orders o 
                    WHERE o.id_user = ? AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
                    AND o.status IN ('delivered', 'confirmed') 
                    GROUP BY MONTH(o.order_date)";
            break;
        case 'year':
            $sql = "SELECT SUM(o.total) as total, YEAR(o.order_date) as year 
                    FROM orders o 
                    WHERE o.id_user = ? AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) 
                    AND o.status IN ('delivered', 'confirmed') 
                    GROUP BY YEAR(o.order_date)";
            break;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$merchant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$merchant_id = $_SESSION['user_id'];
$period = $_GET['period'] ?? 'day'; // Par défaut, afficher les ventes du jour
$salesData = getSalesByPeriod($pdo, $merchant_id, $period);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Statistiques</title>
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
            background: #2c3e50;
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
            background: #34495e;
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
        .period-selector {
            margin-bottom: 2rem;
        }
        .period-selector button {
            background: #2c3e50;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 0.5rem;
        }
        .period-selector button:hover {
            background: #34495e;
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
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .stat-card {
            background-color: #fff;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .stat-card h3 {
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
            color: #333;
        }
        .number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
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
                <h1>Statistiques</h1>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>

            <div class="period-selector">
                <a href="statistiques.php?period=day"><button>Jour</button></a>
                <a href="statistiques.php?period=week"><button>Semaine</button></a>
                <a href="statistiques.php?period=month"><button>Mois</button></a>
                <a href="statistiques.php?period=year"><button>Année</button></a>
            </div>

            <!-- Affichage des statistiques -->
            <?php foreach ($salesData as $data): ?>
                <div class="stat-card">
                    <h3>Total des ventes <?php echo ucfirst($period); ?></h3>
                    <div class="number"><?php echo number_format($data['total'], 2); ?> FCFA</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>
