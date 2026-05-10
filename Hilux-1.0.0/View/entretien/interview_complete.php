<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entretien Terminé</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            text-align: center;
            color: white;
            animation: fadeIn 0.6s ease-out;
        }
        
        h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 300;
            letter-spacing: 2px;
        }
        
        p {
            font-size: 24px;
            opacity: 0.9;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Merci</h1>
        <p>Votre entretien a été enregistré avec succès.</p>
    </div>
</body>
</html>
