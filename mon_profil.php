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
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #3498db;
            color: white;
            padding: 2rem;
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
            background: #2980b9;
        }
        .main-content {
            flex: 1;
            padding: 2rem;
            background: #f8f9fa;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
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