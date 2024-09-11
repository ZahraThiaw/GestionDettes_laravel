<!DOCTYPE html>
<html>
<head>
    <title>Informations du Client</title>
</head>
<body>
    <h1>Informations du Client</h1>
    <p><strong>Nom :</strong> {{ $client->user->nom }}</p>
    <p><strong>Prénom :</strong> {{ $client->user->prenom }}</p>

    <p><strong>Photo :</strong></p>

<p><strong>Code QR :</strong></p>
<img src="data:image/png;base64,{{ base64_encode(file_get_contents($qrCodeFileName)) }}" alt="Code QR" style="width: 150px;">

</body>
</html>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte de fidélité</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            height: 100vh;
            width: 100vw;
            font-family: 'Roboto', sans-serif;
            text-align: center;
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            width: 350px;
            height: 520px;
            background-color: #fff;
            border-radius: 7px;
            box-shadow: 0 0 50px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            align-items: center;
        }
        #pattern1 {
            width: 160%;
            height: 75%;
            object-fit: cover;
            position: relative;
            top: -20%;
            left: 17%;
        }
        #pattern2 {
            width: 100%;
            height: 35%;
            position: relative;
            z-index: 17;
            top: -8%;
            object-fit: cover;
        }
        span {
            font-size: 25px;
            color: #be53fc;
            font-weight: bolder;
            position: absolute;
            top: 7%;
        }
        .avatar {
            width: 134px;
            height: 134px;
            border-radius: 50%;
            background-color: black;
            overflow: hidden;
            margin-bottom: 15px;
            z-index: 19;
            position: absolute;
            top: 15%;
            display: flex;
            justify-content: center;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .qr-code {
            width: 160px;
            height: 160px;
            position: absolute;
            top: 75%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 20;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: white;
            color: #fff;
            font-size: 16px;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 0 25px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .qr-code img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Carte de fidélité -->
    <div class="card">
        <img id="pattern1" src="/assets/pattern-waves.svg" alt="Pattern waves background">
        <img id="pattern2" src="/assets/flat-geometric-background_23-2148948420-removebg-preview 1.svg" alt="Geometric background">
        <!-- Titre de la carte -->
        <span>Carte De Fidélité</span>
        <div class="avatar">
            <img src="{{ $clientPhoto }}" alt="Profile photo of Seydina Mouhammad Diop">
        </div>
        <span style="top: 45%; font-size: 20px; font-weight: 600; color: #404040;">{{ $client->user->nom }} {{ $client->user->prenom }}</span></span>
        <div class="qr-code">
            <img src="{{ $qrCode }}" alt="QR Code">
        </div>
    </div>
</body>
</html>