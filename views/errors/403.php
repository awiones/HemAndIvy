<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden - Hem & Ivy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #f9f9f9 60%, #e9e3f4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            padding: 60px 36px 48px 36px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 40px rgba(75,40,109,0.10), 0 1.5px 6px rgba(75,40,109,0.04);
            max-width: 420px;
            width: 100%;
            position: relative;
        }
        .error-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: var(--imperial-purple, #4B286D);
            letter-spacing: 2px;
        }
        .error-logo span {
            color: var(--aged-gold, #C9A050);
            font-style: italic;
            margin: 0 2px;
        }
        .error-code {
            font-size: 54px;
            font-family: 'Playfair Display', serif;
            color: var(--imperial-purple, #4B286D);
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: 2px;
        }
        .error-title {
            font-size: 26px;
            margin-bottom: 12px;
            color: var(--imperial-purple, #4B286D);
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }
        .error-message {
            color: #6c5a8a;
            margin-bottom: 32px;
            font-size: 15px;
            line-height: 1.7;
        }
        .error-actions {
            margin-top: 10px;
        }
        .error-actions a {
            display: inline-block;
            margin: 0 8px;
            padding: 12px 32px;
            border-radius: 30px;
            background: linear-gradient(90deg, var(--imperial-purple, #4B286D) 80%, var(--aged-gold, #C9A050) 100%);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            box-shadow: 0 2px 8px rgba(75,40,109,0.10);
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        .error-actions a:hover {
            background: linear-gradient(90deg, #5d3485 80%, var(--aged-gold, #C9A050) 100%);
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 4px 16px rgba(75,40,109,0.13);
        }
        @media (max-width: 520px) {
            .error-container {
                padding: 36px 10px 28px 10px;
                max-width: 98vw;
            }
            .error-actions a {
                padding: 12px 18px;
                font-size: 14px;
            }
            .error-logo {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-logo">
            Hem <span>&</span> Ivy
        </div>
        <div class="error-code">403</div>
        <div class="error-title">Forbidden</div>
        <div class="error-message">
            You do not have permission to access this page.<br>
            Please contact the administrator if you believe this is an error.
        </div>
        <div class="error-actions">
            <a href="/home">Go Home</a>
            <a href="javascript:history.back()">Go Back</a>
        </div>
    </div>
</body>
</html>
