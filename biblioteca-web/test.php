<?php
// test.php - Para verificar rutas
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/biblioteca-web/';
echo "Base URL: " . $base_url . "<br>";
echo "CSS URL: " . $base_url . "css/style.css<br>";
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css">
</head>
<body>
    <div style="background: red; color: white; padding: 20px;">
        <h1>TEST RUTAS</h1>
        <p>Si este texto es blanco sobre rojo: CSS NO carga</p>
        <p>Si este texto tiene el estilo del login: CSS SÍ carga</p>
    </div>
</body>
</html>