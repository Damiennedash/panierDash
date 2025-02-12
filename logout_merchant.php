<?php
// Démarrer la session
session_start();

// Détruire toutes les données de la session
session_destroy();

// Rediriger l'utilisateur vers la page de connexion
header("Location: login.php");
exit(); // Assurez-vous que le script s'arrête après la redirection
?>