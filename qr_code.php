<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Veuillez vous connecter.");
}

$qrCodePath = isset($_GET['qr_code']) ? $_GET['qr_code'] : null;

if (!$qrCodePath) {
    die("Code QR non trouvé.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code QR de Paiement</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }
        .qr-container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .qr-container img {
            max-width: 100%;
            height: auto;
        }
        .download-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .download-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <h2>Votre Code QR de Paiement</h2>
        <img src="<?= htmlspecialchars($qrCodePath) ?>" alt="Code QR de Paiement">
        <button class="download-btn" onclick="downloadQRCode()">Télécharger le Code QR</button>
    </div>

    <script>
        function downloadQRCode() {
            const qrCodePath = "<?= htmlspecialchars($qrCodePath) ?>";
            const link = document.createElement('a');
            link.href = qrCodePath;
            link.download = 'qrcode_paiement.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>