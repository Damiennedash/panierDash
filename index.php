<?php
session_start();
require_once 'config.php';

// Récupérer les articles récemment ajoutés
try {
    $query = "SELECT p.*, u.name as merchant_name 
              FROM products p 
              JOIN users u ON p.id_merchant = u.id_user 
              ORDER BY p.id_product DESC 
              LIMIT 10";
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
            background: url('background.jpg') no-repeat center center/cover;
            background-color: rgba(0, 0, 0, 0.5); /* Transparence */
            color: #fff;
            overflow-x: hidden;
            min-height: 100vh; /* Assure que le body prend toute la hauteur de l'écran */
        }

        /* Header */
        header {
            background: linear-gradient(90deg,rgb(242, 215, 178), #D8840E, #C70C0C); /* Dégradé */
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

        header .auth-buttons {
            display: flex;
            gap: 10px;
        }

        header .auth-buttons .btn {
            padding: 8px 16px;
            background-color: #24416B; /* Bleu */
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-size: 0.9rem;
            transition: background-color 0.3s, transform 0.3s;
        }

        header .auth-buttons .btn:hover {
            background-color: #1A2F4F; /* Bleu plus foncé */
            transform: translateY(-3px);
        }

        /* Hero Section */
        .hero {
            text-align: center;
            padding: 150px 20px 80px; /* Ajustement pour le header fixe */
            background: url('background.jpg') no-repeat center center/cover;
            background-color: rgba(0, 0, 0, 0.5); /* Transparence */
            color: #fff;
            position: relative;
            overflow: hidden;
            min-height: 100vh; /* Prend toute la hauteur de l'écran */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
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

        /* Products Section */
        .products {
            padding: 60px 20px;
            background-color: rgba(0, 0, 0, 0.7); /* Transparence */
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 15px 0;
        }

        .product-card {
            background: #333;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.5);
        }

        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 10px;
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
            padding: 10px 15px;
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

        /* About Section */
        .about {
            padding: 60px 20px;
            background-color: rgba(0, 0, 0, 0.9); /* Transparence */
            text-align: center;
        }

        .about h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #fff;
        }

        .about p {
            font-size: 1.2rem;
            color: #ccc;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Contact Section */
        .contact {
            padding: 60px 20px;
            background-color: rgba(0, 0, 0, 0.9); /* Transparence */
            text-align: center;
        }

        .contact h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #fff;
        }

        .contact p {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 10px;
        }

        .contact a {
            color:rgb(31, 106, 211); /* Bleu */
            text-decoration: none;
            transition: color 0.3s;
        }

        .contact a:hover {
            color:rgb(15, 96, 218); /* Bleu plus foncé */
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
            header nav {
                flex-direction: column;
                gap: 10px;
            }
            header .auth-buttons {
                flex-direction: column;
                gap: 10px;
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
            .about h2 {
                font-size: 1.8rem;
            }
            .about p {
                font-size: 1rem;
            }
            .contact h2 {
                font-size: 1.8rem;
            }
            .contact p {
                font-size: 1rem;
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
            .about h2 {
                font-size: 1.5rem;
            }
            .about p {
                font-size: 0.9rem;
            }
            .contact h2 {
                font-size: 1.5rem;
            }
            .contact p {
                font-size: 0.9rem;
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
        <img src="logo_Dash.png" alt="Logo" class="logo">
        <nav>
            <a href="#accueil">Accueil</a>
            <a href="#apropos">À propos</a>
            <a href="#contact">Contactez-nous</a>
        </nav>
        <div class="auth-buttons">
            <a href="login.php" class="btn">Se connecter</a>
            <a href="register.php" class="btn">S'inscrire</a>
            
        </div>
    </header>

    <section id="accueil" class="hero">
        <h1>Bienvenue sur Vide Grenier</h1>
        <p>Découvrez les produits disponibles et faites vos achats en ligne !</p>
    </section>

    <section class="products">
        <h2>Produits récents</h2>
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p><strong>Prix :</strong> <?php echo htmlspecialchars($product['price']); ?> FCFA</p>
                    <p><strong>Quantité disponible :</strong> <?php echo htmlspecialchars($product['available_quantity']); ?></p>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'Student'): ?>
                        <a href="register.php?id=<?php echo $product['id_product']; ?>" class="btn">Commander</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="apropos" class="about">
        <h2>À propos</h2>
        <p>Nous sommes une entreprise qui excelle dans les relations clients étudiants à Lomé Business School. Nous mettons en avant le commerce des étudiants sous une forme plus digitalisée et simple.</p>
    </section>

    <section id="contact" class="contact">
        <h2>Contactez-nous</h2>
        <p>Téléphone : <a href="https://wa.me/22893365551">+228 93365551</a></p>
        <p>Email : <a href="mailto:djata.damienne@lomebs.com">djata.damienne@lomebs.com</a></p>
    </section>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>