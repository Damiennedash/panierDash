<?php
session_start();
require_once "config.php";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':email' => $email]);
        
        if($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_type'] = $user['user_type'];
                
                if($user['user_type'] === 'Merchant') {
                    header("Location: merchant_dashboard.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit();
            }
        }
        $error = "Email ou mot de passe incorrect";
    } catch(PDOException $e) {
        $error = "Erreur de connexion: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <style>
        body {
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
    background: url('background.jpg') no-repeat center center/cover;
    background-color: rgba(0, 0, 0, 0.5); /* Transparence */
    color: #fff;
    overflow-x: hidden;
    min-height: 100vh; /* Assure que le body prend toute la hauteur de l'écran */
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

/* Conteneur du formulaire */
.container {
    max-width: 400px;
    width: 90%;
    margin: 20px auto;
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    color: #333;
}

/* Formulaire */
.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
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

/* Messages d'erreur et de succès */
.error {
    color: red;
    margin-bottom: 10px;
}

.success {
    color: green;
    margin-bottom: 10px;
    background: #e8f5e9;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #c8e6c9;
}

/* ✅ RESPONSIVENESS */
@media (max-width: 768px) {
    .container {
        width: 95%;
        padding: 15px;
    }

    input {
        padding: 8px;
        font-size: 0.9rem;
    }

    button {
        padding: 8px;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    body {
        padding: 10px;
    }

    .container {
        width: 100%;
        padding: 15px;
        box-shadow: none; /* Suppression de l'ombre pour plus de fluidité */
    }

    h2 {
        font-size: 1.2rem;
    }

    input {
        font-size: 0.8rem;
        padding: 7px;
    }

    button {
        font-size: 0.8rem;
        padding: 7px;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Se connecter</button>
        </form>
        <p style="text-align: center;">
            <a href="register.php">Pas encore inscrit ? Créer un compte</a>
        </p>
    </div>
</body>
</html>