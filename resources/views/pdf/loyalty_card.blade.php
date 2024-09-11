<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte De Fidélité</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
            position: relative;
            margin: 0;
        }
        .card {
            top: 25%;
            left: 25%;
            background-color: white;
            border-radius: 10px;
            padding: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 95.6mm; /* Largeur standard de carte de crédit */
            height: 80.98mm; /* Hauteur standard de carte de crédit */
            position: relative;
            overflow: hidden;
        }
        .card-header {
            color: #9932CC;
            font-size: 30px; /* Réduction de la taille du texte */
            margin-bottom: 8px;
        }
        .profile-image {
            width: 80px; /* Taille réduite de l'image */
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 5px;
        }
        .client-name {
            font-size: 25px; /* Réduction de la taille du texte */
            font-weight: bold;
            margin-bottom: 5px;
        }
        .qr-code {
            width: 110px; /* Taille réduite du QR code */
            height: 110px;
            margin: 0 auto;
        }
        .decoration {
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            z-index: 0;
        }
        .decoration-1 {
            background-color: rgba(153, 50, 204, 0.2);
            top: 5px;
            left: 5px;
        }
        .decoration-2 {
            background-color: rgba(255, 165, 0, 0.2);
            bottom: 5px;
            right: 5px;
        }
        .decoration-3 {
            background-color: rgba(255, 255, 255, 0.);
            top: 5px;
            right: 5px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>
        <div class="decoration decoration-3"></div>
        <h1 class="card-header">Carte De Fidélité</h1>
        <img src="{{ $client->user->photo }}" alt="Photo de profil" class="profile-image">
        <p class="client-name">{{ $client->user->prenom }} {{ $client->user->nom }}</p>
        <img src="{{ $client->qrcode}}" alt="QR Code" class="qr-code">
    </div>
</body>
</html>