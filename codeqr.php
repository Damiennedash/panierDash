<?php
class QRCodeGenerator {
    private $ip;
    private $targetUrl;
    private $qrCodeUrl;

    public function __construct($ip) {
        $this->ip = htmlspecialchars($ip);
        $this->targetUrl = "http://" . $this->ip . "/index.php";
        $this->qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($this->targetUrl);
    }

    public function getQrCodeUrl() {
        return $this->qrCodeUrl;
    }
}

// Remplacez par votre adresse IP locale
$qrCode = new QRCodeGenerator("192.168.1.100/vide");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code</title>
</head>
<body>
    <h1>QR Code</h1>
    <p>Scannez ce QR code pour être redirigé vers notre page d'accueil :</p>
    <img src="<?= htmlspecialchars($qrCode->getQrCodeUrl()) ?>" alt="QR Code">
</body>
</html>