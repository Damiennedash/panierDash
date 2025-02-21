<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $query = "INSERT INTO users (name, email, phone, password, user_type) VALUES (:name, :email, :phone, :password, :user_type)";
        $stmt = $pdo->prepare($query);
        
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            ':name' => $_POST['name'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':password' => $password_hash,
            ':user_type' => $_POST['user_type']
        ]);
        
        session_start();
        $_SESSION['success_message'] = "Inscription réussie! Vous pouvez maintenant vous connecter.";
        header("Location: login.php");
        exit();
    } catch(PDOException $e) {
        $error = "Erreur d'inscription: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <style>
         body {
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
        background: url('background.jpg') no-repeat center center/cover;
        background-color: rgba(0, 0, 0, 0.5); /* Transparence */
        color: #fff;
        overflow-x: hidden;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    /* Header */
    header {
        background: linear-gradient(90deg, #FFFFFF, #467FD1, #24416B);
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

    /* Container principal */
    .container {
        max-width: 400px;
        margin: 80px auto 20px; /* Ajout de marge en haut pour éviter que le header recouvre */
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 90%;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        color: #333;
    }

    input, select {
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

    /* ✅ Responsiveness */
    @media (max-width: 768px) {
        header {
            flex-direction: column;
            text-align: center;
            padding: 10px;
        }

        header nav {
            flex-direction: column;
            gap: 10px;
        }

        .container {
            width: 95%;
        }
    }

    @media (max-width: 480px) {
        header {
            padding: 10px 5px;
        }

        header .logo {
            height: 40px;
        }

        header nav {
            gap: 5px;
        }

        header nav a {
            font-size: 0.9rem;
        }

        .container {
            padding: 15px;
        }

        h2 {
            font-size: 1.2rem;
        }

        button {
            padding: 8px;
        }
    }

    </style>
</head>
<body>
    <div class="container">
        <h2>Inscription</h2>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" name="phone">
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Type d'utilisateur</label>
                <select name="user_type" required>
                    <option value="Student">Étudiant</option>
                    <option value="Merchant">Marchand</option>
                </select>
            </div>
            
            <button type="submit">S'inscrire</button>
        </form>
        <p style="text-align: center;">
            <a href="login.php">Déjà inscrit ? Connectez-vous</a>
        </p>
    </div>
</body>
</html>