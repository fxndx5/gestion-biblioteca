<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../api/login.php');
    exit();
}

require_once '../includes/conexion.php';

// Función para verificar permisos
function esAdministrador() {
    return isset($_SESSION['usuario_cargo']) && $_SESSION['usuario_cargo'] === 'admin';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_prestamo'])) {
    // Solo empleados y admin pueden crear préstamos
    if (!esAdministrador() && $_SESSION['usuario_cargo'] !== 'empleado') {
        $_SESSION['error_message'] = 'No tiene permisos para crear préstamos';
        header('Location: prestamos.php');
        exit();
    }
    
    $id_libro = $_POST['id_libro'];
    $id_cliente = $_POST['id_cliente'];
    $fecha_devolucion_estimada = $_POST['fecha_devolucion_estimada'];
    $observaciones = $_POST['observaciones'];
    $id_empleado = $_SESSION['usuario_id'];

    // Verificar disponibilidad del libro
    $sql_check = "SELECT disponibles FROM libros WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_libro);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $libro = $result_check->fetch_assoc();
    $stmt_check->close();
    
    if ($libro['disponibles'] <= 0) {
        $_SESSION['error_message'] = 'El libro no está disponible para préstamo';
        header('Location: prestamos.php');
        exit();
    }

    $sql = "INSERT INTO prestamos (id_libro, id_cliente, id_empleado, fecha_devolucion_estimada, observaciones) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $id_libro, $id_cliente, $id_empleado, $fecha_devolucion_estimada, $observaciones);
    
    if ($stmt->execute()) {
        // Actualizar disponibilidad del libro
        $sql_update = "UPDATE libros SET disponibles = disponibles - 1 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $id_libro);
        $stmt_update->execute();
        $stmt_update->close();
        
        $_SESSION['success_message'] = 'Préstamo registrado exitosamente';
    } else {
        $_SESSION['error_message'] = 'Error al registrar el préstamo: ' . $conn->error;
    }
    
    $stmt->close();
    header('Location: prestamos.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['devolver_prestamo'])) {
    // Solo empleados y admin pueden devolver préstamos
    if (!esAdministrador() && $_SESSION['usuario_cargo'] !== 'empleado') {
        $_SESSION['error_message'] = 'No tiene permisos para devolver préstamos';
        header('Location: prestamos.php');
        exit();
    }
    
    $id_prestamo = $_POST['id_prestamo'];
    
    // Obtener el libro del préstamo
    $sql_get_libro = "SELECT id_libro FROM prestamos WHERE id = ?";
    $stmt_get = $conn->prepare($sql_get_libro);
    $stmt_get->bind_param("i", $id_prestamo);
    $stmt_get->execute();
    $result_get = $stmt_get->get_result();
    $prestamo = $result_get->fetch_assoc();
    $stmt_get->close();
    
    $sql = "UPDATE prestamos SET fecha_devolucion_real = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_prestamo);
    
    if ($stmt->execute()) {
        // Devolver el libro al inventario
        $sql_update = "UPDATE libros SET disponibles = disponibles + 1 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $prestamo['id_libro']);
        $stmt_update->execute();
        $stmt_update->close();
        
        $_SESSION['success_message'] = 'Devolución registrada exitosamente';
    } else {
        $_SESSION['error_message'] = 'Error al registrar la devolución: ' . $conn->error;
    }
    
    $stmt->close();
    header('Location: prestamos.php');
    exit();
}

$sql_prestamos = "SELECT p.*, l.titulo as libro_titulo, c.nombre as cliente_nombre, e.nombre as empleado_nombre,
                  CASE 
                      WHEN p.fecha_devolucion_real IS NOT NULL THEN 'devuelto'
                      WHEN CURDATE() > p.fecha_devolucion_estimada THEN 'atrasado'
                      ELSE 'activo'
                  END as estado
                  FROM prestamos p
                  JOIN libros l ON p.id_libro = l.id
                  JOIN clientes c ON p.id_cliente = c.id
                  JOIN empleados e ON p.id_empleado = e.id
                  ORDER BY p.fecha_prestamo DESC";
$result_prestamos = $conn->query($sql_prestamos);

$sql_libros = "SELECT * FROM libros WHERE disponibles > 0 ORDER BY titulo";
$result_libros = $conn->query($sql_libros);

$sql_clientes = "SELECT * FROM clientes ORDER BY nombre";
$result_clientes = $conn->query($sql_clientes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Préstamos - Biblioteca</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-brand">
                    <img src="../images/logo.png" class="logo-image" alt="Logo Biblioteca">
                </a>
            </div>
            
            <div class="user-info">
                <p><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?> 
                    <span class="admin-badge">
                        <?php echo htmlspecialchars($_SESSION['usuario_cargo']); ?>
                    </span>
                </p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="../dashboard.php">
                            <span>Inicio</span>
                        </a>
                    </li>
                    <li>
                        <a href="libros.php">
                            <span>Libros</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="prestamos.php">
                            <span>Préstamos</span>
                        </a>
                    </li>
                    <li>
                        <a href="clientes.php">
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="categorias.php">
                            <span>Categorías</span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <span>Salir</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <header class="dashboard-header">
                <h1>Gestión de Préstamos</h1>
                <div class="header-actions">
                    <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
                    <button id="btnNuevoPrestamo" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Préstamo
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-warning" onclick="generarReporte()">
                        <i class="fas fa-chart-bar"></i> Reporte
                    </button>
                </div>
            </header>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <?php
                // Estadísticas de préstamos
                $sql_activos = "SELECT COUNT(*) as total FROM prestamos WHERE fecha_devolucion_real IS NULL";
                $result_activos = $conn->query($sql_activos);
                $activos = $result_activos ? $result_activos->fetch_assoc()['total'] : 0;
                
                $sql_atrasados = "SELECT COUNT(*) as total FROM prestamos WHERE fecha_devolucion_real IS NULL AND CURDATE() > fecha_devolucion_estimada";
                $result_atrasados = $conn->query($sql_atrasados);
                $atrasados = $result_atrasados ? $result_atrasados->fetch_assoc()['total'] : 0;
                
                $sql_hoy = "SELECT COUNT(*) as total FROM prestamos WHERE DATE(fecha_prestamo) = CURDATE()";
                $result_hoy = $conn->query($sql_hoy);
                $hoy = $result_hoy ? $result_hoy->fetch_assoc()['total'] : 0;
                
                $sql_mes = "SELECT COUNT(*) as total FROM prestamos WHERE MONTH(fecha_prestamo) = MONTH(CURDATE()) AND YEAR(fecha_prestamo) = YEAR(CURDATE())";
                $result_mes = $conn->query($sql_mes);
                $mes = $result_mes ? $result_mes->fetch_assoc()['total'] : 0;
                ?>
                <div class="stat-card">
                    <h3>Préstamos Activos</h3>
                    <p class="stat-number"><?php echo $activos; ?></p>
                    <p>En circulación</p>
                </div>
                <div class="stat-card">
                    <h3>Préstamos Atrasados</h3>
                    <p class="stat-number"><?php echo $atrasados; ?></p>
                    <p>Pendientes</p>
                </div>
                <div class="stat-card">
                    <h3>Préstamos Hoy</h3>
                    <p class="stat-number"><?php echo $hoy; ?></p>
                    <p>Realizados hoy</p>
                </div>
                <div class="stat-card">
                    <h3>Préstamos Este Mes</h3>
                    <p class="stat-number"><?php echo $mes; ?></p>
                    <p>Total del mes</p>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-list"></i> Historial de Préstamos</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Libro</th>
                                <th>Cliente</th>
                                <th>Fecha Préstamo</th>
                                <th>Devolución Estimada</th>
                                <th>Devolución Real</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_prestamos && $result_prestamos->num_rows > 0): ?>
                                <?php while ($prestamo = $result_prestamos->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge"><?php echo $prestamo['id']; ?></span></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($prestamo['libro_titulo']); ?></strong>
                                        <br><small>Empleado: <?php echo htmlspecialchars($prestamo['empleado_nombre']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($prestamo['cliente_nombre']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($prestamo['fecha_prestamo'])); ?></td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_estimada'])); ?>
                                        <?php if ($prestamo['estado'] === 'atrasado'): ?>
                                            <br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> Atrasado</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($prestamo['fecha_devolucion_real']): ?>
                                            <span class="text-success">
                                                <i class="fas fa-check"></i> <?php echo date('d/m/Y H:i', strtotime($prestamo['fecha_devolucion_real'])); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-warning">
                                                <i class="fas fa-clock"></i> Pendiente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $prestamo['estado']; ?>">
                                            <?php if ($prestamo['estado'] === 'activo'): ?>
                                                <i class="fas fa-play-circle"></i>
                                            <?php elseif ($prestamo['estado'] === 'atrasado'): ?>
                                                <i class="fas fa-exclamation-triangle"></i>
                                            <?php else: ?>
                                                <i class="fas fa-check-circle"></i>
                                            <?php endif; ?>
                                            <?php echo $prestamo['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" onclick="verDetalles(<?php echo $prestamo['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (($prestamo['estado'] === 'activo' || $prestamo['estado'] === 'atrasado') && (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado')): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="id_prestamo" value="<?php echo $prestamo['id']; ?>">
                                                <button type="submit" name="devolver_prestamo" class="btn btn-sm btn-success" onclick="return confirm('¿Registrar devolución de este préstamo?')">
                                                    <i class="fas fa-undo"></i> Devolver
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <?php if (esAdministrador()): ?>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarPrestamo(<?php echo $prestamo['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-exchange-alt fa-3x"></i>
                                            <p>No hay préstamos registrados</p>
                                            <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
                                            <button id="btnNuevoPrestamo2" class="btn btn-primary mt-3">
                                                <i class="fas fa-plus"></i> Registrar Primer Préstamo
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
    <div id="modalPrestamo" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-handshake"></i> Nuevo Préstamo</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="formPrestamo">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="id_libro"><i class="fas fa-book"></i> Libro:</label>
                            <select id="id_libro" name="id_libro" required class="form-control">
                                <option value="">Seleccione un libro disponible</option>
                                <?php if ($result_libros && $result_libros->num_rows > 0): 
                                    $result_libros->data_seek(0); // Reset pointer
                                    while ($libro = $result_libros->fetch_assoc()): ?>
                                        <option value="<?php echo $libro['id']; ?>">
                                            <?php echo htmlspecialchars($libro['titulo']) . ' - ' . htmlspecialchars($libro['autor']); ?>
                                            (Disponibles: <?php echo $libro['disponibles']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay libros disponibles</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="id_cliente"><i class="fas fa-user"></i> Cliente:</label>
                            <select id="id_cliente" name="id_cliente" required class="form-control">
                                <option value="">Seleccione un cliente</option>
                                <?php if ($result_clientes && $result_clientes->num_rows > 0):
                                    $result_clientes->data_seek(0); // Reset pointer
                                    while ($cliente = $result_clientes->fetch_assoc()): ?>
                                        <option value="<?php echo $cliente['id']; ?>">
                                            <?php echo htmlspecialchars($cliente['nombre']) . ' (DNI: ' . $cliente['dni'] . ')'; ?>
                                            <?php if ($cliente['sancionado']): ?>
                                                [SANCIONADO]
                                            <?php endif; ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fecha_devolucion_estimada"><i class="fas fa-calendar-alt"></i> Devolución estimada:</label>
                            <input type="date" id="fecha_devolucion_estimada" name="fecha_devolucion_estimada" 
                                   required min="<?php echo date('Y-m-d'); ?>" 
                                   value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>"
                                   class="form-control">
                        </div>
                        <div class="form-group full-width">
                            <label for="observaciones"><i class="fas fa-sticky-note"></i> Observaciones:</label>
                            <textarea id="observaciones" name="observaciones" rows="3" class="form-control" placeholder="Notas adicionales sobre el préstamo"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
                        <button type="submit" name="crear_prestamo" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar Préstamo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
        const modal = document.getElementById('modalPrestamo');
        const btnNuevo = document.getElementById('btnNuevoPrestamo');
        const btnNuevo2 = document.getElementById('btnNuevoPrestamo2');
        const spanClose = document.querySelector('.close');
        const btnCancelar = document.getElementById('btnCancelar');
        const formPrestamo = document.getElementById('formPrestamo');

        function openModal() {
            modal.style.display = 'block';
            document.getElementById('id_libro').focus();
        }

        if (btnNuevo) btnNuevo.onclick = openModal;
        if (btnNuevo2) btnNuevo2.onclick = openModal;

        function closeModal() {
            modal.style.display = 'none';
            if (formPrestamo) formPrestamo.reset();
        }

        if (spanClose) spanClose.onclick = closeModal;
        if (btnCancelar) btnCancelar.onclick = closeModal;

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
        <?php endif; ?>

        function verDetalles(id) {
            alert('Ver detalles del préstamo con ID: ' + id + ' (funcionalidad no implementada)');
        }

        function eliminarPrestamo(id) {
            <?php if (esAdministrador()): ?>
            if (confirm('¿Está seguro de eliminar este registro de préstamo? Esta acción no se puede deshacer.')) {
                alert('Eliminar préstamo con ID: ' + id + ' (funcionalidad no implementada)');
            }
            <?php else: ?>
            alert('Solo el administrador puede eliminar préstamos');
            <?php endif; ?>
        }

        function generarReporte() {
            alert('Generar reporte de préstamos (funcionalidad no implementada)');
        }

        <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
        // Validación del formulario
        if (formPrestamo) {
            formPrestamo.onsubmit = function(e) {
                const libro = document.getElementById('id_libro').value;
                const cliente = document.getElementById('id_cliente').value;
                const fecha = document.getElementById('fecha_devolucion_estimada').value;
                
                if (!libro) {
                    e.preventDefault();
                    alert('Por favor, seleccione un libro');
                    return false;
                }
                
                if (!cliente) {
                    e.preventDefault();
                    alert('Por favor, seleccione un cliente');
                    return false;
                }
                
                if (!fecha) {
                    e.preventDefault();
                    alert('Por favor, seleccione una fecha de devolución');
                    return false;
                }
                
                // Verificar que la fecha no sea pasada
                const today = new Date().toISOString().split('T')[0];
                if (fecha < today) {
                    e.preventDefault();
                    alert('La fecha de devolución no puede ser anterior a hoy');
                    return false;
                }
                
                return true;
            };
        }
        
        // Configurar fecha mínima
        const fechaInput = document.getElementById('fecha_devolucion_estimada');
        if (fechaInput) {
            fechaInput.min = new Date().toISOString().split('T')[0];
        }
        <?php endif; ?>
    </script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: slideIn 0.3s;
        }
        
        .modal-header {
            background: var(--gradient);
            color: white;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-body {
            padding: 30px;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .close {
            font-size: 28px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .close:hover {
            color: #e74c3c;
            transform: scale(1.1);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            padding-right: 40px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .text-danger {
            color: var(--danger);
        }
        
        .text-warning {
            color: var(--warning);
        }
        
        .text-success {
            color: var(--success);
        }
        
        .mt-3 {
            margin-top: 15px;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</body>
</html>
<?php $conn->close(); ?>