<?php
session_start();

// Vérification de l'authentification et du type d'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Merchant') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$error = '';
$success = '';

// Récupérer l'ID de l'article à modifier
if (!isset($_GET['id'])) {
    header("Location: mes_articles.php");
    exit();
}

$product_id = $_GET['id'];

// Récupérer les informations de l'article
$stmt = $pdo->prepare("SELECT * FROM products WHERE id_product = :id AND id_merchant = :merchant_id");
$stmt->execute([':id' => $product_id, ':merchant_id' => $_SESSION['user_id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: mes_articles.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $quantity = trim($_POST['quantity']);
    $category_name = trim($_POST['category']);

    // Validation des champs
    if (empty($product_name) || empty($description) || empty($price) || empty($quantity) || empty($category_name)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Le prix doit être un nombre positif.";
    } elseif (!is_numeric($quantity) || $quantity < 0) {
        $error = "La quantité doit être un nombre positif ou nulle.";
    } else {
        // Vérification de la catégorie
        $stmt = $pdo->prepare("SELECT id_category FROM categories WHERE name = :name");
        $stmt->execute([':name' => $category_name]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            $error = "La catégorie sélectionnée n'existe pas.";
        } else {
            $id_category = $category['id_category'];

            // Vérifier si une image a été uploadée
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true); // Crée le dossier s'il n'existe pas
                }
                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // Vérifier le type de fichier
                $allowed_types = ["jpg", "jpeg", "png", "gif"];
                if (in_array($imageFileType, $allowed_types)) {
                    // Déplacer le fichier uploadé vers le dossier "uploads"
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        // Mise à jour du produit dans la base de données
                        try {
                            $query = "UPDATE products SET product_name = :product_name, description = :description, price = :price, available_quantity = :quantity, id_category = :id_category, image = :image WHERE id_product = :id";
                            $stmt = $pdo->prepare($query);
                            $stmt->execute([
                                ':product_name' => $product_name,
                                ':description' => $description,
                                ':price' => $price,
                                ':quantity' => $quantity,
                                ':id_category' => $id_category,
                                ':image' => $target_file,
                                ':id' => $product_id
                            ]);

                            $success = "Produit mis à jour avec succès !";
                        } catch (PDOException $e) {
                            $error = "Erreur lors de la mise à jour du produit : " . $e->getMessage();
                        }
                    } else {
                        $error = "Erreur lors de l'upload de l'image.";
                    }
                } else {
                    $error = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
                }
            } else {
                // Si aucune nouvelle image n'est uploadée, on garde l'ancienne
                $target_file = $product['image'];
                try {
                    $query = "UPDATE products SET product_name = :product_name, description = :description, price = :price, available_quantity = :quantity, id_category = :id_category WHERE id_product = :id";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([
                        ':product_name' => $product_name,
                        ':description' => $description,
                        ':price' => $price,
                        ':quantity' => $quantity,
                        ':id_category' => $id_category,
                        ':id' => $product_id
                    ]);

                    $success = "Produit mis à jour avec succès !";
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour du produit : " . $e->getMessage();
                }
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT name FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier un Article</title>
    <style>
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

    header .auth-buttons {
      display: flex;
      gap: 10px;
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
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
            padding: 10px;
            background: #ffebee;
            border-radius: 4px;
            border: 1px solid #ffcdd2;
        }
        .success {
            color: green;
            margin-bottom: 10px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 4px;
            border: 1px solid #c8e6c9;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: #c0392b;
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
                <h1>Modifier un Article</h1>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>

            <div class="container">
                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nom du produit</label>
                        <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Prix (en FCFA)</label>
                        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Quantité disponible</label>
                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($product['available_quantity']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="category" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image du produit</label>
                        <input type="file" name="image" accept="image/*">
                        <p>Image actuelle : <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Image du produit" style="max-width: 100px;"></p>
                    </div>
                    <button type="submit">Mettre à jour le produit</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>