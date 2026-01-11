<?php
// debug.php
echo "<h2>Debug Info:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br><br>";

echo "<h3>Rutas absolutas:</h3>";
echo "CSS: " . realpath('css/style.css') . "<br>";
echo "CSS existe: " . (file_exists('css/style.css') ? 'SÍ' : 'NO') . "<br>";
echo "JS: " . realpath('js/main.js') . "<br>";
echo "JS existe: " . (file_exists('js/main.js') ? 'SÍ' : 'NO') . "<br>";

echo "<h3>Contenido del CSS (primeras 10 líneas):</h3>";
if (file_exists('css/style.css')) {
    $lines = file('css/style.css');
    for ($i = 0; $i < 10 && $i < count($lines); $i++) {
        echo htmlspecialchars($lines[$i]) . "<br>";
    }
}
?>