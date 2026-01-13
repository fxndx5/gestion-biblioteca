<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit();
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit();
}

// Buscar clientes sancionados por DNI, nombre o ID
$sql = "SELECT id, dni, nombre, email, telefono, 
               DATE_FORMAT(fecha_registro, '%d/%m/%Y') as fecha_registro,
               sancionado,
               fecha_fin_sancion,
               CASE 
                   WHEN fecha_fin_sancion IS NOT NULL AND fecha_fin_sancion < CURDATE() THEN 'expirada'
                   WHEN fecha_fin_sancion IS NOT NULL AND fecha_fin_sancion >= CURDATE() THEN 'activa'
                   ELSE 'indefinida'
               END as estado_sancion
        FROM clientes
        WHERE sancionado = 0 
          AND (dni LIKE ? 
               OR nombre LIKE ? 
               OR id LIKE ?)
        ORDER BY fecha_fin_sancion ASC, nombre ASC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$search = "%$query%";
$stmt->bind_param("sss", $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$clientes_sancionados = [];
while ($row = $result->fetch_assoc()) {
    $clientes_sancionados[] = [
        'id' => $row['id'],
        'cliente_nombre' => htmlspecialchars($row['nombre']),
        'cliente_dni' => $row['dni'],
        'cliente_email' => $row['email'],
        'cliente_telefono' => $row['telefono'],
        'fecha_registro' => $row['fecha_registro'],
        'fecha_fin_sancion' => $row['fecha_fin_sancion'] 
            ? date('d/m/Y', strtotime($row['fecha_fin_sancion'])) 
            : 'Indefinida',
        'estado' => $row['estado_sancion'],
        'dias_restantes' => $row['fecha_fin_sancion'] 
            ? round((strtotime($row['fecha_fin_sancion']) - time()) / (60 * 60 * 24)) 
            : null
    ];
}

echo json_encode($clientes_sancionados);

$stmt->close();
$conn->close();
?>