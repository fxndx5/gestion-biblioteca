<?php
echo "<h1>Verificando rutas:</h1>";
echo "Directorio actual: " . __DIR__ . "<br>";
echo "URL actual: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br><br>";

echo "Rutas a probar:<br>";
echo "1. <a href='css/style.css'>css/style.css</a><br>";
echo "2. <a href='index.php'>index.php</a><br>";
echo "3. <a href='modulos/dashboard.php'>modulos/dashboard.php</a><br>";
echo "4. <a href='api/login.php'>api/login.php</a><br>";
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: blue; color: white;">
    <h1>TEST CSS</h1>
    <p>Si esta página tiene fondo azul y texto blanco: CSS NO carga</p>
    <p>Si esta página tiene el estilo del login: CSS SÍ carga</p>
</body>
</html>