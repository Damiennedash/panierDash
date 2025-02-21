<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un marchand

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Récupérer l'ID du marchand connecté
$id_merchant = $_SESSION['user_id'];

// Requête SQL pour récupérer les articles du marchand
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.id_category = c.id_category 
        WHERE p.id_merchant = ?";
$stmt = $pdo->prepare($sql);

// Vérifier si la préparation de la requête a réussi
if (!$stmt) {
    die("Erreur de préparation de la requête : " . $pdo->errorInfo()[2]);
}

// Exécuter la requête avec l'ID du marchand
$stmt->execute([$id_merchant]);

// Récupérer tous les résultats sous forme de tableau associatif
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Articles</title>
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
        background-color: rgba(0, 0, 0, 0.5); /* Transparence */
        color: #fff;
        overflow-x: hidden;
        min-height: 100vh; /* Assure que le body prend toute la hauteur de l'écran */
    }

    /* HEADER */
    header {
        background: linear-gradient(90deg, #FFFFFF, #467FD1, #24416B); /* Dégradé */
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

    /* CONTAINER PRINCIPAL */
    .dashboard {
        display: flex;
        width: 100%;
        min-height: 100vh;
        padding-top: 60px; /* Pour éviter le chevauchement avec le header */
    }

    /* SIDEBAR */
    .sidebar {
        width: 250px;
        background: #3498db;
        color: white;
        padding: 20px;
        position: fixed;
        top: 60px;
        left: 0;
        height: calc(100vh - 60px);
        overflow-y: auto;
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
        background: #34495e;
    }

    /* CONTENU PRINCIPAL */
    .main-content {
        flex: 1;
        margin-left: 250px; /* Pour éviter le chevauchement avec la sidebar */
        padding: 20px;
        background: #f8f9fa;
        color: black;
        overflow-y: auto;
    }

    /* BOUTON AJOUTER UN ARTICLE */
    .add-btn {
        background: #28a745;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        display: inline-block;
        transition: background 0.3s;
    }

    .add-btn:hover {
        background: #218838;
    }

    /* TABLEAU */
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    table th, table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    table th {
        background: #f8f9fa;
    }

    table tr:hover {
        background: #f1f1f1;
    }

    .actions a {
        color: #007bff;
        text-decoration: none;
        margin-right: 10px;
        transition: color 0.3s;
    }

    .actions a:hover {
        text-decoration: underline;
        color: #0056b3;
    }

    /* FOOTER */
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
            <a href="index.php">Accueil</a>
            <a href="about.php">À propos</a>
            <a href="logout.php">Déconnexion</a>
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
            <h1>Mes Articles</h1>
            <a href="ajouter_article.php" class="add-btn">Ajouter un Article</a>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Catégorie</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($result)): ?>
                    <tr>
                        <td colspan="6">Aucun article trouvé.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['description'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['price'] ?? 'N/A') ?>FCFA</td>
                        <td><?= htmlspecialchars($row['available_quantity'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
                        <td class="actions">
                            <a href="modifier_article.php?id=<?= $row['id_product'] ?>">Modifier</a>
                            <a href="supprimer_article.php?id=<?= $row['id_product'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>