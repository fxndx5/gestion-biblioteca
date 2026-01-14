<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$id_cliente = $_POST['id_cliente'] ?? null;
$id_libro = $_POST['id_libro'] ?? null;
$fecha_devolucion_estimada = $_POST['fecha_devolucion_estimada'] ?? null;
$observaciones = $_POST['observaciones'] ?? '';
$id_empleado = $_SESSION['usuario_id'];

if (!$id_cliente || !$id_libro || !$fecha_devolucion_estimada) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
    exit();
}

$sql_cliente = "SELECT id, nombre, sancionado, fecha_fin_sancion FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql_cliente);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();

if (!$cliente) {
    echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
    exit();
}

if ($cliente['sancionado'] == 0) {
    $fecha_sancion = $cliente['fecha_fin_sancion'] ? date('d/m/Y', strtotime($cliente['fecha_fin_sancion'])) : '';
    echo json_encode([
        'success' => false, 
        'message' => "El cliente {$cliente['nombre']} está sancionado" . ($fecha_sancion ? " hasta el $fecha_sancion" : "")
    ]);
    exit();
}
$sql_libro = "SELECT id, titulo, disponibles FROM libros WHERE id = ?";
$stmt = $conn->prepare($sql_libro);
$stmt->bind_param("i", $id_libro);
$stmt->execute();
$result = $stmt->get_result();
$libro = $result->fetch_assoc();

if (!$libro) {
    echo json_encode(['success' => false, 'message' => 'Libro no encontrado']);
    exit();
}

if ($libro['disponibles'] <= 0) {
    echo json_encode(['success' => false, 'message' => "El libro '{$libro['titulo']}' no tiene ejemplares disponibles"]);
    exit();
}

$conn->begin_transaction();

try {
    $sql_prestamo = "INSERT INTO prestamos (id_libro, id_cliente, id_empleado, fecha_prestamo, fecha_devolucion_estimada, estado, observaciones) 
                     VALUES (?, ?, ?, NOW(), ?, 'activo', ?)";
    
    $stmt = $conn->prepare($sql_prestamo);
    
    if (!$stmt) {
        throw new Exception('Error en prepare: ' . $conn->error);
    }
    
    $stmt->bind_param("iiiss", $id_libro, $id_cliente, $id_empleado, $fecha_devolucion_estimada, $observaciones);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar INSERT: ' . $stmt->error);
    }
    
    $id_prestamo_nuevo = $conn->insert_id;
    
    if ($id_prestamo_nuevo == 0) {
        throw new Exception('INSERT ejecutado pero insert_id es 0');
    }
    $sql_update_libro = "UPDATE libros SET disponibles = disponibles - 1 WHERE id = ?";
    $stmt = $conn->prepare($sql_update_libro);
    $stmt->bind_param("i", $id_libro);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar libro: ' . $stmt->error);
    }
    
   
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "✓ Préstamo #$id_prestamo_nuevo registrado correctamente",
        'id_prestamo' => $id_prestamo_nuevo,
        'libro' => $libro['titulo'],
        'cliente' => $cliente['nombre'],
        'fecha_devolucion' => date('d/m/Y', strtotime($fecha_devolucion_estimada))
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>