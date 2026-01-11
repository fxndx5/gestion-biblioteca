<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Biblioteca Universitaria</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-image: url('images/logo-biblioteca.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            padding: 40px;
            border-radius: 15px;
            border-top: 5px solid #0056b3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Biblioteca Universitaria</h1>
            <form id="loginForm">
                <div class="form-group">
                    <label for="usuario"><i class="fas fa-user"></i> Usuario:</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-login">Iniciar Sesión</button>
                <div id="mensajeError" class="error-message"></div>
            </form>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>