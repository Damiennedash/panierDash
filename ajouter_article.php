<?php
session_start();


require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $quantity = trim($_POST['quantity']);
    $category_name = trim($_POST['category']);

   
    if (empty($product_name) || empty($description) || empty($price) || empty($quantity) || empty($category_name)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Le prix doit être un nombre positif.";
    } elseif (!is_numeric($quantity) || $quantity < 0) {
        $error = "La quantité doit être un nombre positif ou nulle.";
    } else {
        
        $stmt = $pdo->prepare("SELECT id_category FROM categories WHERE name = :name");
        $stmt->execute([':name' => $category_name]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            $error = "La catégorie sélectionnée n'existe pas.";
        } else {
            $id_category = $category['id_category'];
            $merchant_id = $_SESSION['user_id']; 

            
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
                        // Insertion du produit dans la base de données
                        try {
                            $query = "INSERT INTO products (product_name, description, price, available_quantity, id_category, id_merchant, image) 
                                      VALUES (:product_name, :description, :price, :quantity, :id_category, :merchant_id, :image)";
                            $stmt = $pdo->prepare($query);
                            $stmt->execute([
                                ':product_name' => $product_name,
                                ':description' => $description,
                                ':price' => $price,
                                ':quantity' => $quantity,
                                ':id_category' => $id_category,
                                ':merchant_id' => $merchant_id,
                                ':image' => $target_file
                            ]);

                            $success = "Produit ajouté avec succès !";
                        } catch (PDOException $e) {
                            $error = "Erreur lors de l'ajout du produit : " . $e->getMessage();
                        }
                    } else {
                        $error = "Erreur lors de l'upload de l'image.";
                    }
                } else {
                    $error = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
                }
            } else {
                $error = "Veuillez sélectionner une image.";
            }
        }
    }
}

// Récupérer les catégories
$stmt = $pdo->prepare("SELECT name FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un Article</title>
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
        header nav {
            margin-top: 10px;
        }
        header nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
        }
        header nav a:hover {
            text-decoration: underline;
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
            <h2>Menu</h2>
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
                <h1>Ajouter un Article</h1>
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
                        <input type="text" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Prix (en FCFA)</label>
                        <input type="number" step="1" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Quantité disponible</label>
                        <input type="number" name="quantity" required>
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
                        <label>Image du produit (obligatoire)</label>
                        <input type="file" name="image" accept="image/*" required>
                    </div>
                    <button type="submit">Ajouter le produit</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>