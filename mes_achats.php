<?php
session_start();
require_once 'config.php'; // Connexion à la base de données avec PDO

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Veuillez vous connecter.");
}

$userId = $_SESSION['user_id'];

// 🛒 Récupérer les commandes en attente ("In Progress") 
$orderSql = "
    SELECT o.id_order, o.id_product, o.order_date, 
           p.product_name, p.price, u.name AS merchant_name, o.quantity, 
           u.id_user AS merchant_id
    FROM orders o
    JOIN products p ON o.id_product = p.id_product
    JOIN users u ON p.id_merchant = u.id_user
    WHERE o.id_student = :user_id 
    AND o.status = 'In Progress'  -- Seules les commandes non confirmées s'affichent
    ORDER BY u.name, o.order_date DESC
";
$orderStmt = $pdo->prepare($orderSql);
$orderStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

try {
    $orderStmt->execute();
    $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des commandes : " . $e->getMessage());
}

// 📌 Organiser les commandes par marchand et calculer le total par marchand
$merchantOrders = [];
$merchantTotals = [];

foreach ($orders as $order) {
    $merchantName = $order['merchant_name'];
    $merchantOrders[$merchantName][] = $order;

    if (!isset($merchantTotals[$merchantName])) {
        $merchantTotals[$merchantName] = 0;
    }
    $merchantTotals[$merchantName] += $order['price'] * $order['quantity'];
}

// 🗑️ Suppression d'une commande
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order_id'])) {
    $deleteOrderId = $_POST['delete_order_id'];

    $deleteSql = "DELETE FROM orders WHERE id_order = :id_order AND id_student = :id_student";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->bindValue(':id_order', $deleteOrderId, PDO::PARAM_INT);
    $deleteStmt->bindValue(':id_student', $userId, PDO::PARAM_INT);

    try {
        $deleteStmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erreur lors de la suppression de la commande : " . $e->getMessage() . "</p>";
    }
}

// 🔄 Mise à jour de la quantité d'une commande
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity_id'])) {
    $updateOrderId = $_POST['update_quantity_id'];
    $newQuantity = (int) $_POST['new_quantity'];

    if ($newQuantity > 0) {
        $updateSql = "UPDATE orders SET quantity = :quantity
                      WHERE id_order = :id_order AND id_student = :id_student";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindValue(':quantity', $newQuantity, PDO::PARAM_INT);
        $updateStmt->bindValue(':id_order', $updateOrderId, PDO::PARAM_INT);
        $updateStmt->bindValue(':id_student', $userId, PDO::PARAM_INT);

        try {
            $updateStmt->execute();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Erreur lors de la mise à jour de la quantité : " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>La quantité doit être un nombre positif.</p>";
    }
}

// ✅ Confirmation de paiement (toutes les commandes d'un même marchand)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_orders'])) {
    $numero = trim($_POST['numero']);
    $merchantId = (int) $_POST['merchant_id']; // Récupération de l'ID du marchand concerné

    // Vérifier que le numéro est valide
    if (!preg_match('/^(96|97|98|99|78|79|90|91|92|93|70|71)\d{6}$/', $numero)) {
        echo "<p style='color: red;'>Numéro invalide.</p>";
    } else {
        // Mettre à jour toutes les commandes du même marchand
        $updateSql = "UPDATE orders 
                      SET numero = :numero, 
                          total = quantity * (SELECT price FROM products WHERE products.id_product = orders.id_product), 
                          status = 'Confirmed'  -- Mettre à jour le statut ici
                      WHERE id_student = :id_student AND status = 'In Progress'
                      AND id_product IN (SELECT id_product FROM products WHERE id_merchant = :merchant_id)";
        
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindValue(':numero', $numero, PDO::PARAM_STR);
        $updateStmt->bindValue(':id_student', $userId, PDO::PARAM_INT);
        $updateStmt->bindValue(':merchant_id', $merchantId, PDO::PARAM_INT);

        try {
            $updateStmt->execute();
            echo "<p>Paiement confirmé avec succès !</p>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Erreur lors de la confirmation du paiement : " . $e->getMessage() . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Achats</title>
    <style>
     * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
        background: url('background.jpg') no-repeat center center/cover;
        background-color: rgba(0, 0, 0, 0.5);
        color: #fff;
        overflow-x: hidden;
        min-height: 100vh;
    }

    /* HEADER */
    header {
        background: linear-gradient(90deg, #FFFFFF, #467FD1, #24416B); /* Dégradé */
        color: #fff;
        padding: 10px 15px;
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
        gap: 15px;
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

    .sidebar h2 { font-size: 22px; }
    .sidebar ul { list-style: none; padding: 0; }
    .sidebar li { margin-bottom: 15px; }
    .sidebar a { color: white; text-decoration: none; padding: 12px; display: block; border-radius: 8px; font-size: 16px; transition: background 0.3s; }
    .sidebar a:hover { background-color: #1f6fb2; }

    /* Main content */
    .main-content {
        margin-left: 270px;
        padding: 30px;
        background-color: rgba(0, 0, 0, 0.5); /* Transparence */
        color: #000;
    }

    .merchant-section {
        background-color: white;
        padding: 25px;
        margin-bottom: 25px;
        border-radius: 8px;
        box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
    }
    .merchant-section h3 {
        margin-bottom: 20px;
        font-size: 22px;
        color: #2c3e50;
    }

    /* Table styling */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 2px solid #ddd;
        font-size: 16px;
    }
    th {
        background-color: #3498db;
        color: white;
    }

    /* Button styling */
    .btn {
        padding: 12px 20px;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s ease;
    }
    .btn-pay { background-color: #27ae60; }
    .btn-pay:hover { background-color: #2ecc71; }
    .btn-delete { background-color: #e74c3c; }
    .btn-delete:hover { background-color: #c0392b; }
    .btn-close { background-color: #f39c12; }
    .btn-close:hover { background-color: #e67e22; }

    /* Styles du popup */
    .popup-checkbox {
        display: none;
    }

    .popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: center;
        align-items: center;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .popup-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        width: 300px;
    }

    .popup-buttons {
        margin-top: 20px;
        display: flex;
        justify-content: space-around;
    }

    .btn-confirm {
        background-color: #27ae60; /* Vert */
        padding: 10px 20px;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s ease;
    }

    .btn-confirm:hover {
        background-color: #2ecc71; /* Vert plus clair au survol */
    }

    .btn-cancel {
        background-color: #e74c3c; /* Rouge */
        padding: 10px 20px;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s ease;
    }

    .btn-cancel:hover {
        background-color: #c0392b; /* Rouge plus foncé au survol */
    }

    .operator-icons {
        display: flex;
        justify-content: space-around;
        margin-top: 15px;
    }

    .operator-btn {
        background: none;
        border: none;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .operator-btn img {
        width: 80px;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .operator-btn:hover {
        transform: scale(1.1);
    }

    /* Afficher le popup lorsque la checkbox est cochée */
    .popup-checkbox:checked + .popup {
        visibility: visible;
        opacity: 1;
    }
   /* Style du loader */
   .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            display: none; /* Caché par défaut */
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Afficher le loader lorsque le formulaire est valide */
        #moov-payment-form:valid ~ #loader-moov,
        #mixx-payment-form:valid ~ #loader-mixx {
            display: block; /* Affiche le loader */
        }

        /* Masquer le bouton "Confirmer" lorsque le formulaire est valide */
        #moov-payment-form:valid ~ .btn-confirm,
        #mixx-payment-form:valid ~ .btn-confirm {
            display: none; /* Masque le bouton */
        }

        /* Animation pour simuler un délai */
        @keyframes delayLoader {
            0% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; }
        }

        /* Redirection après l'animation */
        #moov-payment-form:valid ~ #redirect-link-moov,
        #mixx-payment-form:valid ~ #redirect-link-mixx {
            display: inline-block;
            animation: delayLoader 3s forwards;
        }

</style>
</head>
<body>
    <header>
        <img src="logo_Dash.png" alt="Logo" class="logo">  
        Mes Achats
    </header>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Menu Étudiant</h2>
            <ul>
                <li><a href="student_dashboard.php">Accueil</a></li>
                <li><a href="suivi_commande.php">Mes commandes validées</a></li>
                <li><a href="mes_achats.php">Mes Achats</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="mon_profil.php">Mon Profil</a></li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <h2>Historique des Commandes</h2>

            <?php foreach ($merchantOrders as $merchant => $orders): ?>
                <div class="merchant-section">
                    <h3>Marchand : <?= htmlspecialchars($merchant) ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Commande</th>
                                <th>Produit</th>
                                <th>Prix</th>
                                <th>Quantité</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id_order'] ?></td>
                                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                                    <td><?= number_format($order['price'], 2) ?> FCFA</td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="update_quantity_id" value="<?= $order['id_order'] ?>">
                                            <input type="number" name="new_quantity" value="<?= $order['quantity'] ?>" min="1" required>
                                            <button type="submit" class="btn btn-pay">Mettre à jour</button>
                                        </form>
                                    </td>
                                    <td><?= $order['order_date'] ?></td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="delete_order_id" value="<?= $order['id_order'] ?>">
                                            <button type="submit" class="btn btn-delete">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Bouton pour ouvrir le pop-up de paiement pour tout le marchand -->
                    <label for="pay-popup-trigger-<?= md5($merchant) ?>" class="btn btn-pay">Payer</label>

                    <!-- Input checkbox caché pour contrôler le pop-up de confirmation -->
                    <input type="checkbox" id="pay-popup-trigger-<?= md5($merchant) ?>" class="popup-checkbox">

                    <!-- Pop-up de confirmation -->
                    <div class="popup confirmation-popup">
                        <div class="popup-content">
                            <h3>Confirmer le paiement pour <?= htmlspecialchars($merchant) ?> ?</h3>
                            <div class="popup-buttons">
                                <label for="operator-popup-trigger-<?= md5($merchant) ?>" class="btn btn-confirm">Oui</label>
                                <label for="pay-popup-trigger-<?= md5($merchant) ?>" class="btn btn-cancel">Non</label>
                            </div>
                        </div>
                    </div>

                    <!-- Input checkbox caché pour le choix de l'opérateur -->
                    <input type="checkbox" id="operator-popup-trigger-<?= md5($merchant) ?>" class="popup-checkbox">

                    <!-- Pop-up de choix d'opérateur -->
                    <div class="popup operator-popup">
                        <div class="popup-content">
                            <h3>Choisissez votre opérateur</h3>
                            <div class="operator-icons">
                                <label for="moov-popup-trigger-<?= md5($merchant) ?>" class="operator-btn">
                                    <img src="moov_togo.jpg" alt="Moov">
                                </label>
                                <label for="mixx-popup-trigger-<?= md5($merchant) ?>" class="operator-btn">
                                    <img src="mixx_togo.jpg" alt="Mixx">
                                </label>
                            </div>
                            <label for="operator-popup-trigger-<?= md5($merchant) ?>" class="btn btn-cancel">Annuler</label>
                        </div>
                    </div>

                    <!-- Pop-up de paiement Moov -->
                    <input type="checkbox" id="moov-popup-trigger-<?= md5($merchant) ?>" class="popup-checkbox">
                    <div class="popup moov-popup">
                        <div class="popup-content">
                            <h3>Paiement Moov - <?= htmlspecialchars($merchant) ?></h3>
                            <form id="moov-payment-form" method="POST" action="">
                                <input type="hidden" name="confirm_orders" value="1">
                                <input type="hidden" name="merchant_id" value="<?= $order['merchant_id'] ?>">
                                <input type="hidden" name="operator" value="moov">
                                <label for="numero">Numéro de téléphone :</label>
                                <input type="text" name="numero" placeholder="Ex : 99995665" required pattern="^(96|97|98|99|78|79)\d{6}$">
                                <p>Total : <strong><?= number_format($merchantTotals[$merchant], 2) ?> FCFA</strong></p>
                                <button type="submit" class="btn btn-confirm" >Confirmer</button>
                            </form>
                            <!-- Loader pour Moov -->
                            <div id="loader-moov" class="loader"></div>
                        </div>
                    </div>
                    <!-- Redirection après animation pour Moov -->
                    <a id="redirect-link-moov" href="studentdashboard" style="display: none;"></a>

                    <!-- Pop-up de paiement Mixx -->
                    <input type="checkbox" id="mixx-popup-trigger-<?= md5($merchant) ?>" class="popup-checkbox">
                    <div class="popup mixx-popup">
                        <div class="popup-content">
                            <h3>Paiement Mixx - <?= htmlspecialchars($merchant) ?></h3>
                            <form id="mixx-payment-form" method="POST" action="">
                                <input type="hidden" name="confirm_orders" value="1">
                                <input type="hidden" name="merchant_id" value="<?= $order['merchant_id'] ?>">
                                <input type="hidden" name="operator" value="mixx">
                                <label for="numero">Numéro de téléphone :</label>
                                <input type="text" name="numero" placeholder="Ex : 91995665" required pattern="^(90|91|92|93|70|71)\d{6}$">
                                <p>Total : <strong><?= number_format($merchantTotals[$merchant], 2) ?> FCFA</strong></p>
                                <button type="submit" class="btn btn-confirm">Confirmer</button>
                            </form>
                            <!-- Loader pour Mixx -->
                            <div id="loader-mixx" class="loader"></div>
                        </div>
                    </div>
                    <!-- Redirection après animation pour Mixx -->
                    <a id="redirect-link-mixx" href="studentdashboard" style="display: none;"></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>