<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "digital_flea_market";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// 1. Nombre total de commandes
$total_orders_query = "SELECT COUNT(*) AS total_orders FROM orders";
$total_orders_result = $conn->query($total_orders_query);
$total_orders = $total_orders_result->fetch_assoc()['total_orders'];

// 2. Revenus totaux
$total_revenue_query = "SELECT SUM(amount) AS total_revenue FROM payments";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'];

// 3. Revenus par méthode de paiement
$revenue_by_payment_query = "SELECT payment_method, SUM(amount) AS total_revenue FROM payments GROUP BY payment_method";
$revenue_by_payment_result = $conn->query($revenue_by_payment_query);

// 4. Statuts des commandes
$order_statuses_query = "SELECT status, COUNT(*) AS count FROM orders GROUP BY status";
$order_statuses_result = $conn->query($order_statuses_query);

// 5. Statuts des livraisons
$delivery_statuses_query = "SELECT status, COUNT(*) AS count FROM deliveries GROUP BY status";
$delivery_statuses_result = $conn->query($delivery_statuses_query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #343a40;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
            color: #007bff;
        }
        .card {
            background: #f1f3f5;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 5px solid #007bff;
        }
        .card h2 {
            margin: 0 0 10px;
            font-size: 1.25rem;
            color: #495057;
        }
        .card ul {
            padding-left: 20px;
            list-style-type: square;
        }
        .card li {
            margin: 5px 0;
            font-size: 1rem;
        }
        .card p {
            font-size: 1.25rem;
            font-weight: bold;
        }
        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #868e96;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tableau de Bord</h1>

        <!-- Nombre total de commandes -->
        <div class="card">
            <h2>Total des Commandes</h2>
            <p><?php echo $total_orders; ?></p>
        </div>

        <!-- Revenus totaux -->
        <div class="card">
            <h2>Revenus Totaux</h2>
            <p><?php echo $total_revenue ? $total_revenue . " €" : "0 €"; ?></p>
        </div>

        <!-- Revenus par méthode de paiement -->
        <div class="card">
            <h2>Revenus par Méthode de Paiement</h2>
            <ul>
                <?php while ($row = $revenue_by_payment_result->fetch_assoc()) { ?>
                    <li><?php echo $row['payment_method'] . " : " . $row['total_revenue'] . " €"; ?></li>
                <?php } ?>
            </ul>
        </div>

        <!-- Statuts des commandes -->
        <div class="card">
            <h2>Statuts des Commandes</h2>
            <ul>
                <?php while ($row = $order_statuses_result->fetch_assoc()) { ?>
                    <li><?php echo $row['status'] . " : " . $row['count']; ?></li>
                <?php } ?>
            </ul>
        </div>

        <!-- Statuts des livraisons -->
        <div class="card">
            <h2>Statuts des Livraisons</h2>
            <ul>
                <?php while ($row = $delivery_statuses_result->fetch_assoc()) { ?>
                    <li><?php echo $row['status'] . " : " . $row['count']; ?></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <footer>
        &copy; 2025 Projet Vide-Grenier Digital. Tous droits réservés.
    </footer>
</body>
</html>

<?php
$conn->close();
?>