<?php
// Démarrage de la session
session_start();

// Détruire toutes les données de la session
session_unset(); // Supprime toutes les variables de session
session_destroy(); // Détruit la session

// Rediriger l'utilisateur vers la page de connexion
header("Location: login.php");
exit();
?>