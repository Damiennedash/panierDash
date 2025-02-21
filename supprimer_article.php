<?php
session_start();

// Vérification de l'authentification et du type d'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Merchant') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Récupérer l'ID de l'article à supprimer
if (!isset($_GET['id'])) {
    header("Location: mes_articles.php");
    exit();
}

$product_id = $_GET['id'];

// Vérifier que l'article appartient bien au marchand
$stmt = $pdo->prepare("SELECT * FROM products WHERE id_product = :id AND id_merchant = :merchant_id");
$stmt->execute([':id' => $product_id, ':merchant_id' => $_SESSION['user_id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: mes_articles.php");
    exit();
}

// Supprimer l'article
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id_product = :id");
        $stmt->execute([':id' => $product_id]);

        // Supprimer l'image associée si elle existe
        if (file_exists($product['image'])) {
            unlink($product['image']);
        }

        header("Location: mes_articles.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression du produit : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supprimer un Article</title>
    <style>
         body {
     font-family: 'Poppins', sans-serif;
     line-height: 1.6;
     background: url('background.jpg') no-repeat center center/cover;
     background-color: rgba(0, 0, 0, 0.5); /* Transparence */
     color: #fff;
     overflow-x: hidden;
     min-height: 100vh; /* Assure que le body prend toute la hauteur de l'écran */
        }

        /* Header */
    header {
     background: linear-gradient(90deg,#FFFFFF, #467FD1, #24416B); /* Dégradé */
     color: #fff;
     padding: 15px 20px;
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
     gap: 20px;
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
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
        }
        .sidebar h2 {
            margin-bottom: 20px;
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
            background: #34495e;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            height: 100px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        button:active {
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .cancel-btn {
            display: inline-block;
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }

        .cancel-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .cancel-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .logout-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <img src="logo_Dash.png" alt="Logo" class="logo">
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
            <h2>Menu</h2>
            <ul>
                <?php if ($_SESSION['user_type'] === 'Merchant'): ?>
                    <li><a href="merchant_dashboard.php">Tableau de bord</a></li>
                    <li><a href="mes_articles.php">Mes Articles</a></li>
                    <li><a href="ajouter_article.php">Ajouter un Article</a></li>
                    <li><a href="commandes.php">Commandes</a></li>
                <?php else: ?>
                    <li><a href="student_dashboard.php">Tableau de bord</a></li>
                    <li><a href="mes_achats.php">Mes Achats</a></li>
                <?php endif; ?>
                <li><a href="messages.php">Messages</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Supprimer un Article</h1>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>

            <div class="container">
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <p>Êtes-vous sûr de vouloir supprimer l'article "<?php echo htmlspecialchars($product['product_name']); ?>" ?</p>
                <form method="POST" action="">
                    <button type="submit">Supprimer</button>
                    <a href="mes_articles.php" class="cancel-btn">Annuler</a>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>