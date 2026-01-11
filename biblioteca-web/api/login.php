<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
        exit();
    }
    
    $sql = "SELECT id, nombre, cargo FROM empleados 
            WHERE usuario = ? 
            AND password = SHA2(?, 256) 
            AND activo = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $empleado = $result->fetch_assoc();
        $_SESSION['usuario_id'] = $empleado['id'];
        $_SESSION['usuario_nombre'] = $empleado['nombre'];
        $_SESSION['usuario_cargo'] = $empleado['cargo'];
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>