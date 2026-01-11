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

$sql = "SELECT id, dni, nombre, email, sancionado 
        FROM clientes 
        WHERE dni LIKE ? OR nombre LIKE ?
        ORDER BY nombre ASC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$searchTerm = "%$query%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

header('Content-Type: application/json');
echo json_encode($clientes);

$stmt->close();
$conn->close();
?>