<?php
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];

// Récupérer les messages reçus et envoyés
try {
    $query = "SELECT m.*, u.name as sender_name 
              FROM messages m 
              JOIN users u ON m.sender_id = u.id_user 
              WHERE m.receiver_id = :user_id 
              ORDER BY m.sent_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $received_messages = $stmt->fetchAll();

    $query = "SELECT m.*, u.name as receiver_name 
              FROM messages m 
              JOIN users u ON m.receiver_id = u.id_user 
              WHERE m.sender_id = :user_id 
              ORDER BY m.sent_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $sent_messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des messages : " . $e->getMessage();
}

// Envoyer un message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    try {
        $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':sender_id' => $user_id,
            ':receiver_id' => $receiver_id,
            ':message' => $message
        ]);

        $success = "Message envoyé avec succès !";
    } catch (PDOException $e) {
        $error = "Erreur lors de l'envoi du message : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messagerie</title>
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
        min-height: 100vh;
        margin-top: 60px; /* Pour éviter le chevauchement avec le header */
    }

    /* SIDEBAR */
    .sidebar {
        width: 250px;
        background: #3498db; /* Utilisation du bleu de commande */
        color: white;
        padding: 2rem;
        position: fixed;
        top: 60px;
        left: 0;
        height: calc(100vh - 60px);
        overflow-y: auto;
    }

    .sidebar h2 {
        margin-bottom: 2rem;
        font-size: 1.5rem;
    }

    .sidebar ul {
        list-style: none;
    }

    .sidebar li {
        margin-bottom: 1rem;
    }

    .sidebar a {
        color: white;
        text-decoration: none;
        display: block;
        padding: 0.5rem;
        border-radius: 5px;
        transition: background 0.3s;
    }

    .sidebar a:hover {
        background: #2980b9; /* Ton bleu plus sombre au survol */
    }

    /* CONTENU PRINCIPAL */
    .main-content {
        flex: 1;
        margin-left: 250px; /* Pour éviter le chevauchement avec la sidebar */
        padding: 2rem;
        background: #f8f9fa;
        color: #000; /* Contenu en noir */
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .message-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .message-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .message-card h3 {
        margin-bottom: 0.5rem;
    }

    .message-card p {
        margin-bottom: 0.5rem;
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

    /* FORMULAIRE */
    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input, textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    textarea {
        resize: vertical;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background: #45a049;
    }

    /* FOOTER */
    footer {
        background: #34495e; /* Utilisation du même bleu pour le pied de page */
        color: white;
        text-align: center;
        padding: 20px 0;
        margin-top: 40px;
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
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="statistiques.php">Statistiques</a></li>
                <?php else: ?>
                    <li><a href="student_dashboard.php">Accueil</a></li>
                    <li><a href="suivi_commande.php">Mes commandes validés</a></li>
                    <li><a href="mes_achats.php">Mes Achats</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="mon_profil.php">Mon Profil</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Messagerie</h1>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>

            <?php if(isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <h2>Messages reçus</h2>
            <div class="message-grid">
                <?php foreach ($received_messages as $message): ?>
                    <div class="message-card">
                        <h3>De : <?php echo htmlspecialchars($message['sender_name']); ?></h3>
                        <p><strong>Date :</strong> <?php echo htmlspecialchars($message['sent_at']); ?></p>
                        <p><?php echo htmlspecialchars($message['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2>Messages envoyés</h2>
            <div class="message-grid">
                <?php foreach ($sent_messages as $message): ?>
                    <div class="message-card">
                        <h3>À : <?php echo htmlspecialchars($message['receiver_name']); ?></h3>
                        <p><strong>Date :</strong> <?php echo htmlspecialchars($message['sent_at']); ?></p>
                        <p><?php echo htmlspecialchars($message['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2>Envoyer un message</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Destinataire</label>
                    <select name="receiver_id" required>
                        <?php
                        // Récupérer la liste des utilisateurs (sauf soi-même)
                        $query = "SELECT id_user, name FROM users WHERE id_user != :user_id";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([':user_id' => $user_id]);
                        $users = $stmt->fetchAll();

                        foreach ($users as $user): ?>
                            <option value="<?php echo $user['id_user']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" required></textarea>
                </div>
                <button type="submit">Envoyer</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>