<?php
// archivo: ajax/eliminar_sancion.php
session_start();

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// VERIFICAR QUE EL USUARIO ES ADMIN
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores pueden eliminar sanciones.']);
    exit();
}

require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$id_cliente = $_POST['id_cliente'] ?? null;

if (!$id_cliente) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente no proporcionado']);
    exit();
}

// Verificar que el cliente existe
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

// Verificar si ya está activo
if ($cliente['sancionado'] == 1) {
    echo json_encode([
        'success' => false, 
        'message' => "El cliente {$cliente['nombre']} no tiene sanciones activas"
    ]);
    exit();
}

// Eliminar la sanción
$sql_eliminar = "UPDATE clientes 
                 SET sancionado = 1, 
                     fecha_fin_sancion = NULL 
                 WHERE id = ?";

$stmt = $conn->prepare($sql_eliminar);
$stmt->bind_param("i", $id_cliente);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "✓ Sanción eliminada correctamente para {$cliente['nombre']} (DNI: {$cliente['dni']})",
        'cliente' => $cliente['nombre']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar la sanción'
    ]);
}

$stmt->close();
$conn->close();
?>