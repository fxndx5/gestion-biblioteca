<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores pueden aplicar sanciones.']);
    exit();
}

require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$id_cliente = $_POST['id_cliente'] ?? null;
$dias_sancion = $_POST['dias_sancion'] ?? 15; 
$motivo = $_POST['motivo'] ?? 'Sanción aplicada por administrador';

if (!$id_cliente) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente no proporcionado']);
    exit();
}

if (!is_numeric($dias_sancion) || $dias_sancion < 1 || $dias_sancion > 365) {
    echo json_encode(['success' => false, 'message' => 'Los días de sanción deben estar entre 1 y 365']);
    exit();
}

$sql_verificar = "SELECT id, nombre, dni, sancionado FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql_verificar);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();

if (!$cliente) {
    echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
    exit();
}

if ($cliente['sancionado'] == 0) {
    echo json_encode([
        'success' => false, 
        'message' => "El cliente {$cliente['nombre']} ya tiene una sanción activa"
    ]);
    exit();
}

$fecha_fin = date('Y-m-d', strtotime("+{$dias_sancion} days"));

$sql_sancionar = "UPDATE clientes 
                  SET sancionado = 0, 
                      fecha_fin_sancion = ? 
                  WHERE id = ?";

$stmt = $conn->prepare($sql_sancionar);
$stmt->bind_param("si", $fecha_fin, $id_cliente);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "✓ Sanción aplicada a {$cliente['nombre']} (DNI: {$cliente['dni']}) hasta el " . date('d/m/Y', strtotime($fecha_fin)),
        'cliente' => $cliente['nombre'],
        'fecha_fin' => date('d/m/Y', strtotime($fecha_fin)),
        'dias' => $dias_sancion
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al aplicar la sanción'
    ]);
}

$stmt->close();
$conn->close();
?>