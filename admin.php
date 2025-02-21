<?php
session_start();

// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=digital_flea_market', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Affichage des erreurs

// Variable pour stocker le message de confirmation
$message = '';

// Récupérer la liste des étudiants
$stmt_etudiants = $pdo->query("
    SELECT u.id_user, u.name, u.email, u.phone
    FROM users u
    WHERE u.user_type = 'Student'
");
$etudiants = $stmt_etudiants->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des marchands avec leur statut d'abonnement
$stmt_marchands = $pdo->query("
    SELECT u.id_user, u.name, u.email, u.phone, m.abonnement
    FROM users u
    LEFT JOIN merchants m ON u.id_user = m.id_user
    WHERE u.user_type = 'Merchant'
");
$marchands = $stmt_marchands->fetchAll(PDO::FETCH_ASSOC);

// Vérification et mise à jour de l'abonnement d'un marchand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_user'])) {
    $id_user = $_POST['id_user'];  // ID de l'utilisateur
    $abonnement = $_POST['abonnement'];    // Nouveau statut d'abonnement

    try {
        // Vérification de l'existence du marchand dans la table merchants
        $stmt_check = $pdo->prepare("
            SELECT m.id_user 
            FROM merchants m 
            WHERE m.id_user = ?
        ");
        $stmt_check->execute([$id_user]);
        $marchand_existe = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($marchand_existe) {
            // Mise à jour de l'abonnement dans la table merchants
            $stmt = $pdo->prepare("UPDATE merchants SET abonnement = ? WHERE id_user = ?");
            $stmt->execute([$abonnement, $id_user]);

            // Après la mise à jour
            if ($stmt->rowCount() > 0) {
            $message = "Mise à jour réussie!";
            // Redirection après 2 secondes
            header("refresh:2;url=admin.php");
            } else {
            $message = "Aucune mise à jour effectuée. Vérifiez l'ID du marchand.";
            // Redirection après 2 secondes
            header("refresh:2;url=admin.php");
            }

        } else {
            // Si le marchand n'existe pas dans la table merchants, on l'ajoute
            $stmt_insert = $pdo->prepare("INSERT INTO merchants (id_user, abonnement) VALUES (?, ?)");
            $stmt_insert->execute([$id_user, $abonnement]);

            if ($stmt_insert->rowCount() > 0) {
                $message = "Marchand ajouté et abonnement mis à jour!";
            } else {
                $message = "Erreur lors de l'ajout du marchand.";
            }
        }
    } catch (Exception $e) {
        $message = "Erreur SQL : " . $e->getMessage();
    }

    // Redirection vers la même page pour éviter la soumission multiple du formulaire
    header("Location: admin.php?message=" . urlencode($message));
    exit();
}

// Récupérer le message de confirmation depuis l'URL
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page d'Administration</title>
    <style>
        /* Styles CSS (identique à votre code précédent) */
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

        /* Header */
        header {
            background: linear-gradient(90deg,#FFFFFF, #467FD1, #24416B);
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
            backdrop-filter: blur(10px);
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
            color: #24416B;
            transform: translateY(-2px);
        }

        header .auth-buttons {
            display: flex;
            gap: 10px;
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
            color: black; /* Texte en noir */
        }

        .main-content .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        /* TABLEAUX */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background: #2c3e50;
            color: white;
        }

        table tr:hover {
            background: #f1f1f1;
        }

        /* FORMULAIRE */
        .action-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .action-form select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .action-form button {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .action-form button:hover {
            background: #218838;
        }
        /* MESSAGE DE CONFIRMATION */
        .confirmation-message {
        padding: 10px;
        margin-bottom: 20px;
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 5px;
        text-align: center;
        animation: fadeOut 2s forwards;
        }

        /* Animation de disparition */
        @keyframes fadeOut {
        0% {
             opacity: 1;
        }
        99% {
             opacity: 1;
        }
        100% {
             opacity: 0;
             visibility: hidden;
        }
       }
        /* PIED DE PAGE */
        footer {
            text-align: center;
            padding: 15px;
            background: #333;
            color: white;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <img src="logo_Dash.png" alt="Logo" class="logo">
        <nav>
            <a href="#">Accueil</a>
            <a href="#">Marchands</a>
            <a href="#">Étudiants</a>
        </nav>
        <div class="auth-buttons">
            <a href="#" class="logout-btn">Déconnexion</a>
        </div>
    </header>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="#">Tableau de bord</a></li>
            </ul>
        </div>

        <!-- Contenu principal -->
        <div class="main-content">
            <div class="header">
                <h1>Page d'Administration</h1>
            </div>

            <!-- Affichage du message de confirmation -->
            <?php if (!empty($message)): ?>
                <div class="confirmation-message">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <h2>Liste des Étudiants</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $etudiant): ?>
                        <tr>
                            <td><?= htmlspecialchars($etudiant['id_user']) ?></td>
                            <td><?= htmlspecialchars($etudiant['name']) ?></td>
                            <td><?= htmlspecialchars($etudiant['email']) ?></td>
                            <td><?= htmlspecialchars($etudiant['phone']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Liste des Marchands</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Abonnement</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($marchands as $marchand): ?>
                        <tr>
                            <td><?= htmlspecialchars($marchand['id_user']) ?></td>
                            <td><?= htmlspecialchars($marchand['name']) ?></td>
                            <td><?= htmlspecialchars($marchand['email']) ?></td>
                            <td><?= htmlspecialchars($marchand['phone']) ?></td>
                            <td><?= htmlspecialchars($marchand['abonnement']) ?></td>
                            <td>
                                <!-- Formulaire pour mettre à jour l'abonnement -->
                                <form method="POST" action="admin.php" class="action-form">
                                    <input type="hidden" name="id_user" value="<?= $marchand['id_user'] ?>">
                                    <select name="abonnement">
                                        <option value="confirmed" <?= $marchand['abonnement'] === 'confirmed' ? 'selected' : '' ?>>Confirmé</option>
                                        <option value="unconfirmed" <?= $marchand['abonnement'] === 'unconfirmed' ? 'selected' : '' ?>>Non Confirmé</option>
                                    </select>
                                    <button type="submit">Mettre à jour</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Digital Flea Market. Tous droits réservés.</p>
    </footer>
</body>
</html>