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
    <img src="{{ $client->user->photo}}" alt="Photo du client" style="width: 150px;">
    <p><strong>Code QR :</strong></p>
    <img src="{{ asset('storage/images/' .'loyalty_card_' . $client->id . '.png') }}" alt="Code QR">
</body>
</html>
