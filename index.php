<?php
session_start();

function obtenerInformacionPC() {
    $info = [
        "Nombre del Equipo" => gethostname(),
        "Dirección IP" => getUserIP(),
        "Nombre de Usuario" => get_current_user(),
        "Navegador" => getBrowser()
    ];
    return $info;
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function getBrowser() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $browser = "Unknown Browser";

    $browserArray = [
        '/msie/i' => 'Internet Explorer',
        '/firefox/i' => 'Firefox',
        '/safari/i' => 'Safari',
        '/chrome/i' => 'Chrome',
        '/edge/i' => 'Edge',
        '/opera/i' => 'Opera',
        '/netscape/i' => 'Netscape',
        '/maxthon/i' => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i' => 'Mobile Browser'
    ];

    foreach ($browserArray as $regex => $value) {
        if (preg_match($regex, $userAgent)) {
            $browser = $value;
        }
    }
    return $browser;
}

function obtenerUbicacionGeografica($ip) {
    $url = "http://ipinfo.io/{$ip}/json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        return json_decode($response, true);
    } else {
        return null;
    }
}

function enviarADiscord($info, $ubicacion) {
    $webhook_url = "https://discord.com/api/webhooks/1262238130854039562/TAPM5PAWi8emw1WuLion6Nu7o8_pHVKXXHyza2CEBVsfVi6ehq_cs490zxXjMAFYiGLB";
    $data = json_encode([
        "content" => "Aquí está la información del bot Piojo-Hub:",
        "embeds" => [
            [
                "title" => "Información del Bot Piojo-Hub",
                "color" => 0x00ff00,
                "fields" => array_merge(
                    array_map(function ($key, $value) {
                        return ["name" => $key, "value" => $value, "inline" => false];
                    }, array_keys($info), $info),
                    array_map(function ($key, $value) {
                        return ["name" => $key, "value" => $value, "inline" => false];
                    }, array_keys($ubicacion), $ubicacion)
                )
            ]
        ]
    ]);

    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => $data
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($webhook_url, false, $context);

    if ($result === FALSE) {
        die('Error al enviar el mensaje a Discord');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'password') {
        $_SESSION['loggedin'] = true;
        $info = obtenerInformacionPC();
        $ubicacion = obtenerUbicacionGeografica($info['Dirección IP']);
        enviarADiscord($info, $ubicacion);
    } else {
        $error = "Nombre de usuario o contraseña incorrectos.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Bot Piojo-Hub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #2e2e2e;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #444444;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        h1 {
            margin-top: 0;
            font-size: 24px;
        }
        .logo-container {
            margin: 20px 0;
        }
        .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        p {
            margin: 5px 0;
            font-size: 16px;
        }
        label {
            margin-top: 10px;
            font-size: 16px;
        }
        input {
            display: block;
            width: calc(100% - 22px);
            margin-bottom: 10px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #555555;
            color: #ffffff;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #666666;
        }
        a {
            display: block;
            text-align: center;
            color: #00ff00;
            text-decoration: none;
            margin-top: 20px;
            font-size: 16px;
        }
        a:hover {
            text-decoration: underline;
        }
        .error {
            color: #ff0000;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <?php
            $info = obtenerInformacionPC();
            $ubicacion = obtenerUbicacionGeografica($info['Dirección IP']);
            ?>
            <h1>Bienvenido, <?php echo htmlspecialchars($info['Nombre de Usuario']); ?></h1>
            <div class="logo-container">
                <img src="https://cdn.discordapp.com/attachments/1262238098012639314/1266298346226843678/Converter.png?ex=66a4a3bc&is=66a3523c&hm=70a98bb273301b34c741efd808e06ab9b8f6b6d7ae6ddae0aeb16837ccc05bef&" alt="Logo del Bot Piojo-Hub" class="logo">
            </div>
            <p>Información del Bot Piojo-Hub</p>
            <a href="?logout=true">Cerrar Sesión</a>
        <?php else: ?>
            <h1>Iniciar Sesión</h1>
            <form method="POST" action="index.php">
                <label for="username">Nombre de Usuario:</label>
                <input type="text" id="username" name="username" required>
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Iniciar Sesión</button>
            </form>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
