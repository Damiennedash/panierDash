<?php
session_start();
require_once 'config.php';

// Récupérer les articles récemment ajoutés
try {
    $query = "SELECT p.*, u.name as merchant_name 
              FROM products p 
              JOIN users u ON p.id_merchant = u.id_user 
              ORDER BY p.id_product DESC 
              LIMIT 5";
    $stmt = $pdo->query($query);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des produits : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vide Grenier - Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            background: #000; /* Fond noir */
            color: #fff;
            overflow-x: hidden;
        }

        /* Header */
        header {
            background: rgba(20, 20, 20, 0.9); /* Transparence */
            color: #fff;
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px); /* Effet de flou */
        }

        header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #e50914; /* Rouge Netflix */
            font-weight: 600;
        }

        header nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        header nav a {
            color: #fff;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s, transform 0.3s;
        }

        header nav a:hover {
            color: #e50914;
            transform: translateY(-2px); /* Effet de levée */
        }

        /* Hero Section */
        .hero {
            text-align: center;
            padding: 150px 20px 80px; /* Ajustement pour le header fixe */
            background: #000; /* Fond noir */
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-in-out;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            animation: fadeIn 2s ease-in-out;
        }

        .hero .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            background-color: #e50914; /* Rouge Netflix */
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-size: 1rem;
            transition: background-color 0.3s, transform 0.3s;
            animation: fadeIn 2.5s ease-in-out;
        }

        .hero .btn:hover {
            background-color: #b20710; /* Rouge plus foncé */
            transform: translateY(-5px);
        }

        /* Products Section */
        .products {
            padding: 60px 20px;
            background: #141414; /* Fond sombre */
        }

        .products h2 {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 40px;
            color: #fff;
            font-weight: 600;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .product-card {
            background: #333;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
        }

        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: transform 0.3s;
        }

        .product-card:hover img {
            transform: scale(1.1);
        }

        .product-card h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #fff;
        }

        .product-card p {
            font-size: 1rem;
            margin-bottom: 10px;
            color: #ccc;
        }

        .product-card .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e50914; /* Rouge Netflix */
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-size: 0.9rem;
            transition: background-color 0.3s, transform 0.3s;
        }

        .product-card .btn:hover {
            background-color: #b20710; /* Rouge plus foncé */
            transform: translateY(-3px);
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 20px;
            background: #000; /* Fond noir */
            color: #fff;
            margin-top: 40px;
        }

        footer p {
            font-size: 0.9rem;
        }

        /* Error Message */
        .error {
            color: #e50914; /* Rouge Netflix */
            text-align: center;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header h1 {
                font-size: 1.8rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .products h2 {
                font-size: 1.8rem;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .product-card h3 {
                font-size: 1.3rem;
            }

            .product-card p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            header h1 {
                font-size: 1.5rem;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .hero p {
                font-size: 0.9rem;
            }

            .products h2 {
                font-size: 1.5rem;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .product-card h3 {
                font-size: 1.2rem;
            }

            .product-card p {
                font-size: 0.8rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
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

    <section class="hero">
        <h1>Bienvenue sur Vide Grenier</h1>
        <p>Découvrez les produits disponibles et faites vos achats en ligne !</p>
        <a href="login.php" class="btn">Se connecter</a>
        <a href="register.php" class="btn">S'inscrire</a>
    </section>

    <section class="products">
        <h2>Produits récents</h2>
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p><strong>Prix :</strong> <?php echo htmlspecialchars($product['price']); ?> €</p>
                    <p><strong>Vendeur :</strong> <?php echo htmlspecialchars($product['merchant_name']); ?></p>
                    <p><strong>Quantité disponible :</strong> <?php echo htmlspecialchars($product['available_quantity']); ?></p>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'Student'): ?>
                        <a href="order.php?id=<?php echo $product['id_product']; ?>" class="btn">Commander</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>