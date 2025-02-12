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
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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