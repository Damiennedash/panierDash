<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Student') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? intval($_GET['category']) : null;

// 🔹 Récupérer les catégories
$categorySql = "SELECT * FROM categories";
$categoryStmt = $pdo->query($categorySql);
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Récupérer les produits disponibles
$params = [];
$productSql = "SELECT p.*, c.name AS category_name 
               FROM products p 
               LEFT JOIN categories c ON p.id_category = c.id_category 
               WHERE p.available_quantity > 0";

if (!empty($searchTerm)) {
    $productSql .= " AND (p.product_name LIKE :search OR p.description LIKE :search)";
    $params['search'] = "%$searchTerm%";
}

if (!empty($selectedCategory)) {
    $productSql .= " AND p.id_category = :category";  
    $params['category'] = $selectedCategory;
}

$productStmt = $pdo->prepare($productSql);
$productStmt->execute($params);
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Traitement de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_product']) && isset($_POST['quantity'])) {
    $id_student = $_SESSION['user_id'];
    $id_product = intval($_POST['id_product']);
    $quantity = intval($_POST['quantity']);

    // Vérifier si le produit existe et est disponible
    $productSql = "SELECT p.id_product, p.product_name, p.price, p.image, p.description, c.name AS category_name, p.id_merchant, p.available_quantity 
                   FROM products p
                   LEFT JOIN categories c ON p.id_category = c.id_category
                   WHERE p.id_product = :id_product";
    $productStmt = $pdo->prepare($productSql);
    $productStmt->bindParam(':id_product', $id_product, PDO::PARAM_INT);
    $productStmt->execute();
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $product['available_quantity'] >= $quantity) {
        try {
            // Insérer la commande
            $orderSql = "INSERT INTO orders (id_student, id_product, quantity, id_user, status) 
                         VALUES (:id_student, :id_product, :quantity, :id_user, 'In Progress')";
            $orderStmt = $pdo->prepare($orderSql);
            $orderStmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $orderStmt->bindParam(':id_product', $id_product, PDO::PARAM_INT);
            $orderStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $orderStmt->bindParam(':id_user', $product['id_merchant'], PDO::PARAM_INT);  // id_merchant est l'id_user du vendeur

            // Vérification d'exécution de la requête
            if ($orderStmt->execute()) {
                // Réduire la quantité disponible
                $updateProductSql = "UPDATE products SET available_quantity = available_quantity - :quantity WHERE id_product = :id_product";
                $updateProductStmt = $pdo->prepare($updateProductSql);
                $updateProductStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $updateProductStmt->bindParam(':id_product', $id_product, PDO::PARAM_INT);
                $updateProductStmt->execute();

                header("Location: student_dashboard.php?success=1");
                exit();
            } else {
                var_dump($orderStmt->errorInfo());  
                echo "❌ Erreur lors de l'enregistrement de la commande.";
            }
        } catch (PDOException $e) {
            echo "❌ Erreur SQL : " . $e->getMessage();
        }
    } else {
        header("Location: student_dashboard.php?error=2"); // Produit non disponible
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Étudiant</title>
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
        padding: 15px 0;
        text-align: center;
        z-index: 1000;
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

    /* BARRE DE RECHERCHE */
    .search-bar {
        margin-bottom: 20px;
    }

    .search-bar form {
        display: flex;
        gap: 10px;
    }

    .search-bar input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    }

    .search-bar button {
        padding: 10px 15px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s, transform 0.2s ease;
    }

    .search-bar button:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }

    .search-bar button:active {
        background: #1f6fa4;
        transform: translateY(2px);
    }

    /* CATEGORIES */
    .categories {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
        justify-content: center;
    }

    .category-card {
        width: 180px;
        height: 50px;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: center;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .category-card a {
        text-decoration: none;
        color: #333;
        font-weight: bold;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    /* LISTE DE PRODUITS */
    .product-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        justify-content: center;
    }

    .product-card {
        width: 250px;
        height: 300px;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
    }

    .product-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 10px;
    }

    /* BOUTON DE COMMANDE */
    .order-btn {
        display: inline-block;
        background: #28a745;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s, transform 0.2s ease;
    }

    .order-btn:hover {
        background: #218838;
        transform: translateY(-2px);
    }

    .order-btn:active {
        background: #1e7e34;
        transform: translateY(2px);
    }

    /* POPUP */
    .popup-checkbox {
        display: none;
    }

    .popup-checkbox:checked + .popup {
        display: flex;
    }

    .popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.1);
        display: none;
        justify-content: center;
        align-items: center;
    }

    .popup-content {
        background: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        width: 300px;
    }

    /* POPUP - CHOIX D'OPÉRATEUR */
    .moov-popup {
        display: none;
    }

    .popup-checkbox:checked + .popup .moov-popup {
        display: block;
    }

    /* BOUTONS DANS LES POPUPS */
    .popup-buttons {
        margin-top: 10px;
    }

    .confirm-btn {
        background: #28a745;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s, transform 0.2s ease;
    }

    .confirm-btn:hover {
        background: #218838;
        transform: translateY(-2px);
    }

    .confirm-btn:active {
        background: #1e7e34;
        transform: translateY(2px);
    }

    .cancel-btn {
        background: #dc3545;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s, transform 0.2s ease;
    }

    .cancel-btn:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    .cancel-btn:active {
        background: #bd2130;
        transform: translateY(2px);
    }

    /* LOADER */
    .loader {
        display: none;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 10px auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* CHAMPS DE FORMULAIRE */
    input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        width: 100%;
        margin-bottom: 10px;
    }

    /* Alerte de succès */
    .alert-success {
        display: none;
        background: #2ecc71;
        color: #fff;
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        margin-top: 10px;
    }
</style>
</head>
<body>
    <header>
        <h1>Vide Grenier</h1>
    </header>

    <div class="dashboard">
        <div class="sidebar">
            <h2>Menu Étudiant</h2>
            <ul>
                <li><a href="student_dashboard.php">Accueil</a></li>
                <li><a href="suivi_commande.php">Mes commandes validés</a></li>
                <li><a href="mes_achats.php">Mes Achats</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="mon_profil.php">Mon Profil</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="search-bar">
                <form action="student_dashboard.php" method="GET">
                    <input type="text" name="search" placeholder="Rechercher des articles..." value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit">Rechercher</button>
                </form>
            </div>

            <div class="categories">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <a href="student_dashboard.php?category=<?= htmlspecialchars($category['id_category']) ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="product-container">
                <div class="product-header">
                    <h2>Produits disponibles</h2>
                </div>

                <div class="product-list">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="Image du produit">
                                <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                                <p><?= htmlspecialchars($product['description']) ?></p>
                                <p>Prix : <?= htmlspecialchars($product['price']) ?> FCFA</p>
                                <p>Catégorie : <?= htmlspecialchars($product['category_name']) ?></p>
                                <form method="POST" action="student_dashboard.php" class="order-form">
                                <input type="hidden" name="id_product" value="<?= htmlspecialchars($product['id_product']) ?>">
    <label for="popup-trigger-<?= htmlspecialchars($product['id_product']) ?>" class="order-btn">
        Commander
    </label>
</form>

<!-- INPUT CACHÉ POUR OUVRIR LE PREMIER POP-UP -->
<input type="checkbox" id="popup-trigger-<?= htmlspecialchars($product['id_product']) ?>" class="popup-checkbox">

<!-- PREMIER POP-UP - CONFIRMATION DE COMMANDE -->
<div class="popup confirmation-popup">
    <div class="popup-content">
        <h3>Êtes-vous sûr de vouloir passer la commande ?</h3>
        <div class="popup-buttons">
            <!-- OUVRE LE DEUXIÈME POP-UP SI L'UTILISATEUR CONFIRME -->
            <label for="operator-popup-trigger-<?= htmlspecialchars($product['id_product']) ?>" class="confirm-btn">Oui</label>
            <!-- FERME LE POP-UP SI L'UTILISATEUR ANNULLE -->
            <label for="popup-trigger-<?= htmlspecialchars($product['id_product']) ?>" class="cancel-btn">Non</label>
        </div>
    </div>
</div>

<!-- INPUT CACHÉ POUR OUVRIR LE POP-UP CHOIX D'OPÉRATEUR -->
<input type="checkbox" id="operator-popup-trigger-<?= htmlspecialchars($product['id_product']) ?>" class="popup-checkbox">
  
<div class="popup moov-popup">
    <div class="popup-content">
        <h3>Finaliser la commande (Moov)</h3>
        <form method="POST" action="student_dashboard.php">
            <input type="hidden" name="id_product" value="<?= htmlspecialchars($product['id_product']) ?>">
            <input type="hidden" name="operator" value="moov">

            <label for="quantity">Quantité :</label>
            <input type="number" name="quantity" min="1" value="1">
            <button type="submit" class="confirm-btn">Confirmer</button>
        </form>
        <div class="alert-success" id="success-message">Commande enregistrée avec succès !</div>
        </form>
    </div>
</div>

<!-- INPUT CACHÉ POUR FERMER LES POP-UPS -->
<input type="checkbox" id="close-popup" class="popup-checkbox">

                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun produit trouvé.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>"


</html>
