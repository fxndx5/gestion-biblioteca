<?php
session_start();


if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'No autorizado - No hay sesión activa'
    ]);
    exit();
}

if (!isset($_SESSION['usuario_cargo'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: No se ha definido el cargo en la sesión. Por favor, cierra sesión y vuelve a entrar.',
        'debug' => 'usuario_cargo no está definido en SESSION'
    ]);
    exit();
}

if ($_SESSION['usuario_cargo'] !== 'admin') {
    echo json_encode([
        'success' => false, 
        'message' => 'Acceso denegado. Solo administradores pueden eliminar sanciones.',
        'debug' => 'Cargo actual: ' . $_SESSION['usuario_cargo'] . ' (se esperaba: admin)'
    ]);
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

if ($cliente['sancionado'] == 1) {
    echo json_encode([
        'success' => false, 
        'message' => "El cliente {$cliente['nombre']} no tiene sanciones activas"
    ]);
    exit();
}

// Elimina la sanción
$sql_eliminar = "UPDATE clientes 
                 SET sancionado = 1, 
                     fecha_fin_sancion = NULL 
                 WHERE id = ?";

$stmt = $conn->prepare($sql_eliminar);
$stmt->bind_param("i", $id_cliente);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "Sanción eliminada correctamente para {$cliente['nombre']} (DNI: {$cliente['dni']})",
        'cliente' => $cliente['nombre']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar la sanción: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>