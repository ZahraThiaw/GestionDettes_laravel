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