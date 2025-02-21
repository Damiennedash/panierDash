<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=digital_flea_market", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Validation de l'ID du produit
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de produit invalide.");
}

$id_product = (int)$_GET['id'];
$id_user = $_SESSION['user_id']; // Utilisation de l'ID de l'utilisateur connecté

// Vérification que l'utilisateur existe et est un étudiant
$sql = "SELECT id_user FROM users WHERE id_user = ? AND user_type = 'Student'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_user]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("ID utilisateur invalide ou l'utilisateur n'est pas un étudiant. Veuillez vous reconnecter.");
}

// Vérifier si l'utilisateur existe dans la table `students`
$sql = "SELECT id_student FROM students WHERE id_student = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_user]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    // Si l'utilisateur n'existe pas dans la table `students`, l'ajouter
    $sql = "INSERT INTO students (id_student) VALUES (?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_user]);
}

// Récupération des détails du produit
$sql = "SELECT * FROM products WHERE id_product = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_product]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produit non trouvé ou ID de produit invalide.");
}

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)$_POST['quantity'];

    // Vérification que la quantité demandée est disponible
    if ($quantity > $product['available_quantity']) {
        die("La quantité demandée n'est pas disponible.");
    }

    // Insertion de la commande dans la table `orders`
    try {
        $sql = "INSERT INTO orders (id_student, id_product, quantity, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_user, $id_product, $quantity]);

        // Redirection vers la page de suivi de commande
        header("Location: suivi_commande.php?id=" . $pdo->lastInsertId());
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la création de la commande : " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Commander un Article</title>
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
        .order-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .order-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .order-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .order-form button {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .order-form button:hover {
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
        <img src="logo_Dash.png" alt="Logo" class="logo">
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
            <h1>Commander un Article</h1>
            <div class="order-form">
                <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p>Prix: <?= htmlspecialchars($product['price']) ?>€</p>
                <p>Quantité disponible: <?= htmlspecialchars($product['available_quantity']) ?></p>
                <form method="POST">
                    <label for="quantity">Quantité:</label>
                    <input type="number" name="quantity" min="1" max="<?= $product['available_quantity'] ?>" required>
                    <button type="submit">Confirmer la commande</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>