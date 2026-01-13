<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once '../includes/conexion.php';

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT l.id, l.titulo, l.autor, l.isbn, l.editorial, 
               l.ejemplares, l.disponibles, l.ubicacion,
               c.nombre as categoria
        FROM libros l
        LEFT JOIN categorias c ON l.id_categoria = c.id
        WHERE l.id LIKE ? 
           OR l.titulo LIKE ? 
           OR l.autor LIKE ? 
           OR l.isbn LIKE ?
        ORDER BY l.titulo ASC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$searchTerm = "%$query%";
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$libros = [];
while ($row = $result->fetch_assoc()) {
    $libros[] = $row;
}

header('Content-Type: application/json');
echo json_encode($libros);

$stmt->close();
$conn->close();
?>