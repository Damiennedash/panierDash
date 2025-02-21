<?php
session_start();

// Vérification de l'authentification et du type d'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Student') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$student_id = $_SESSION['user_id'];

// Récupérer les informations de l'étudiant
try {
    $query = "SELECT * FROM users WHERE id_user = :student_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':student_id' => $student_id]);
    $student = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération du profil : " . $e->getMessage();
}

// Mettre à jour le profil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $query = "UPDATE users SET name = :name, email = :email, phone = :phone WHERE id_user = :student_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':student_id' => $student_id
        ]);

        $success = "Profil mis à jour avec succès !";
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour du profil : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mon Profil</title>
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

/* CONTAINER PRINCIPAL */
.dashboard {
    display: flex;
    min-height: 100vh;
    margin-top: 60px; /* Pour éviter le chevauchement avec le header */
}

/* SIDEBAR */
.sidebar {
    width: 260px; /* Ajusté pour s’aligner avec le contenu */
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
    font-size: 1.4rem;
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
    padding: 12px;
    border-radius: 5px;
    transition: background 0.3s;
}

.sidebar a:hover {
    background: #2980b9;
}

/* CONTENU PRINCIPAL */
.main-content {
    flex: 1;
    margin-left: 260px; /* Alignement avec le sidebar */
    padding: 25px;
    background: #f8f9fa;
    color: #333; /* Couleur du texte lisible */
}

/* FORMULAIRE */
.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-size: 1rem;
    color: #333; /* Texte noir */
}

input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 1rem;
}

button {
    width: 100%;
    padding: 10px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
}

button:hover {
    background: #45a049;
}

.error {
    color: red;
    margin-bottom: 10px;
}

.success {
    color: green;
    margin-bottom: 10px;
}

/* TABLEAU */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background: #3498db;
    color: white;
}

table tr:hover {
    background: #f1f1f1;
}

/* ORDER STATUS */
.order-status {
    font-weight: bold;
}

.order-status.delivered {
    color: #4caf50; /* Vert */
}

.order-status.cancelled {
    color: #f44336; /* Rouge */
}

/* BOUTONS */
.status-buttons button {
    padding: 8px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    margin: 2px;
    color: white;
    transition: background-color 0.3s ease;
}

.status-buttons .delivered {
    background-color: #4caf50; /* Vert */
}

.status-buttons .cancelled {
    background-color: #f44336; /* Rouge */
}

.status-buttons button:hover {
    opacity: 0.8; /* Effet de survol */
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
/* FOOTER */
footer {
        background: #333;
        color: white;
        text-align: center;
        padding: 10px;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        z-index: 50;
    }

footer p {
    margin: 0;
    font-size: 1rem;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .sidebar {
        width: 230px;
    }

    .main-content {
        margin-left: 230px;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 220px;
    }

    .main-content {
        margin-left: 220px;
    }

    header {
        padding: 10px;
    }

    header .logo {
        height: 40px;
    }

    header nav {
        gap: 15px;
    }

    header nav a {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 100%;
        position: relative;
        padding: 10px;
        height: auto;
    }

    .main-content {
        margin-left: 0;
    }

    header {
        flex-direction: column;
        padding: 10px;
    }

    header nav {
        flex-direction: column;
        gap: 10px;
    }
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
            <div class="header">
                <h1>Mon Profil</h1>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>

            <?php if(isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>">
                </div>
                <button type="submit">Mettre à jour</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Vide Grenier. Tous droits réservés.</p>
    </footer>
</body>
</html>