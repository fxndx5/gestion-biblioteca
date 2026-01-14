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

$id_prestamo = $_POST['id_prestamo'] ?? null;
$observaciones_devolucion = $_POST['observaciones_devolucion'] ?? '';

if (!$id_prestamo) {
    echo json_encode(['success' => false, 'message' => 'ID de préstamo no proporcionado']);
    exit();
}

$sql_verificar = "SELECT p.id, p.id_libro, p.fecha_devolucion_estimada, 
                         l.titulo, c.nombre as cliente
                  FROM prestamos p
                  JOIN libros l ON p.id_libro = l.id
                  JOIN clientes c ON p.id_cliente = c.id
                  WHERE p.id = ? AND p.fecha_devolucion_real IS NULL";

$stmt = $conn->prepare($sql_verificar);
$stmt->bind_param("i", $id_prestamo);
$stmt->execute();
$result = $stmt->get_result();
$prestamo = $result->fetch_assoc();

if (!$prestamo) {
    echo json_encode(['success' => false, 'message' => 'Préstamo no encontrado o ya fue devuelto']);
    exit();
}

$conn->begin_transaction();

try {
    $observaciones_final = $observaciones_devolucion 
        ? $observaciones_devolucion 
        : 'Devolución registrada';
    
    $sql_devolucion = "UPDATE prestamos 
                       SET fecha_devolucion_real = CURRENT_TIMESTAMP,
                           estado = 'devuelto',
                           observaciones = CONCAT(IFNULL(observaciones, ''), ' | Devolución: ', ?)
                       WHERE id = ?";
    
    $stmt = $conn->prepare($sql_devolucion);
    $stmt->bind_param("si", $observaciones_final, $id_prestamo);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al registrar la devolución');
    }
    
    //incrementa disponibilidad del libro
    $sql_update_libro = "UPDATE libros SET disponibles = disponibles + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql_update_libro);
    $stmt->bind_param("i", $prestamo['id_libro']);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar disponibilidad del libro');
    }
    
    //verificasi el prestamo se entrego con retraso
    $fecha_dev_estimada = new DateTime($prestamo['fecha_devolucion_estimada']);
    $fecha_actual = new DateTime();
    $dias_retraso = $fecha_actual->diff($fecha_dev_estimada)->days;
    
    $mensaje_adicional = '';
    if ($fecha_actual > $fecha_dev_estimada) {
        $mensaje_adicional = " (Entregado con $dias_retraso días de retraso)";
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => '✓ Devolución registrada correctamente' . $mensaje_adicional,
        'libro' => $prestamo['titulo'],
        'cliente' => $prestamo['cliente']
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