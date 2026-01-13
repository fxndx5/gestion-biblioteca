<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../api/login.php');
    exit();
}

require_once '../includes/conexion.php';

// Función para verificar permisos
function esAdministrador() {
    return isset($_SESSION['usuario_cargo']) && $_SESSION['usuario_cargo'] === 'administrador';
}

function esEmpleado() {
    return isset($_SESSION['usuario_cargo']) && $_SESSION['usuario_cargo'] === 'empleado';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_cliente'])) {
    // Verificar permisos: admin y empleados pueden crear clientes
    if (!esAdministrador() && !esEmpleado()) {
        $_SESSION['error_message'] = 'No tiene permisos para crear clientes';
        header('Location: clientes.php');
        exit();
    }
    
    $dni = trim($_POST['dni']);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    // Validar DNI único
    $sql_check = "SELECT id FROM clientes WHERE dni = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $dni);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $_SESSION['error_message'] = 'Ya existe un cliente con este DNI';
        $stmt_check->close();
        header('Location: clientes.php');
        exit();
    }
    $stmt_check->close();

    $sql = "INSERT INTO clientes (dni, nombre, email, telefono, direccion, fecha_registro, sancionado) 
            VALUES (?, ?, ?, ?, ?, CURDATE(), 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $dni, $nombre, $email, $telefono, $direccion);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Cliente creado exitosamente';
    } else {
        $_SESSION['error_message'] = 'Error al crear el cliente: ' . $conn->error;
    }
    
    $stmt->close();
    header('Location: clientes.php');
    exit();
}

// Verificar si es para eliminar (solo admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_cliente'])) {
    if (!esAdministrador()) {
        $_SESSION['error_message'] = 'Solo el administrador puede eliminar clientes';
        header('Location: clientes.php');
        exit();
    }
    
    $id_cliente = $_POST['id_cliente'];
    
    // Verificar si el cliente tiene préstamos pendientes
    $sql_check_prestamos = "SELECT COUNT(*) as total FROM prestamos WHERE id_cliente = ? AND fecha_devolucion_real IS NULL";
    $stmt_check = $conn->prepare($sql_check_prestamos);
    $stmt_check->bind_param("i", $id_cliente);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $prestamos = $result_check->fetch_assoc();
    $stmt_check->close();
    
    if ($prestamos['total'] > 0) {
        $_SESSION['error_message'] = 'No se puede eliminar el cliente porque tiene préstamos pendientes';
        header('Location: clientes.php');
        exit();
    }
    
    $sql = "DELETE FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Cliente eliminado exitosamente';
    } else {
        $_SESSION['error_message'] = 'Error al eliminar el cliente: ' . $conn->error;
    }
    
    $stmt->close();
    header('Location: clientes.php');
    exit();
}

$sql_clientes = "SELECT * FROM clientes ORDER BY nombre";
$result_clientes = $conn->query($sql_clientes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Biblioteca</title>
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
                    <li>
                        <a href="prestamos.php">
                            <span>Préstamos</span>
                        </a>
                    </li>
                    <li class="active">
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
                <h1>Gestión de Clientes</h1>
                <div class="header-actions">
                    <?php if (esAdministrador() || esEmpleado()): ?>
                    <button id="btnNuevoCliente" class="btn btn-primary">
                        Nuevo Cliente
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-secondary" onclick="window.print()">
                        Imprimir
                    </button>
                </div>
            </header>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="section">
                <h2><i class="fas fa-list"></i>Listado de Clientes</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DNI</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Fecha Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_clientes && $result_clientes->num_rows > 0): ?>
                                <?php while ($cliente = $result_clientes->fetch_assoc()): 
                                    // Contar préstamos activos del cliente
                                    $sql_prestamos = "SELECT COUNT(*) as total FROM prestamos WHERE id_cliente = ? AND fecha_devolucion_real IS NULL";
                                    $stmt_prestamos = $conn->prepare($sql_prestamos);
                                    $stmt_prestamos->bind_param("i", $cliente['id']);
                                    $stmt_prestamos->execute();
                                    $result_prestamos = $stmt_prestamos->get_result();
                                    $prestamos_activos = $result_prestamos->fetch_assoc()['total'];
                                    $stmt_prestamos->close();
                                ?>
                                <tr>
                                    <td><span class="badge"><?php echo $cliente['id']; ?></span></td>
                                    <td><code><?php echo htmlspecialchars($cliente['dni']); ?></code></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>
                                        <?php if ($cliente['direccion']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($cliente['direccion']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <?php if ($cliente['email']): ?>
                                                <div><?php echo htmlspecialchars($cliente['email']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($cliente['telefono']): ?>
                                                <div><?php echo htmlspecialchars($cliente['telefono']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($prestamos_activos > 0): ?>
                                                <div class="text-warning">
                                                    <?php echo $prestamos_activos; ?> préstamo(s) activo(s)
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></td>
                                    <td>
                                        <?php if ($cliente['sancionado']): ?>
                                            <span class="badge badge-danger"><i class="fas fa-ban"></i> Sancionado</span>
                                        <?php elseif ($prestamos_activos > 0): ?>
                                            <span class="badge badge-warning"><i class="fas fa-book"></i> Con préstamos</span>
                                        <?php else: ?>
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Activo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" onclick="verDetallesCliente(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (esAdministrador() || esEmpleado()): ?>
                                            <button class="btn btn-sm btn-warning" onclick="editarCliente(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if (esAdministrador()): ?>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="id_cliente" value="<?php echo $cliente['id']; ?>">
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="confirmarEliminarCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars(addslashes($cliente['nombre'])); ?>', <?php echo $prestamos_activos; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-users fa-3x"></i>
                                            <p>No hay clientes registrados</p>
                                            <?php if (esAdministrador() || esEmpleado()): ?>
                                            <button id="btnNuevoCliente2" class="btn btn-primary mt-3">
                                                <i class="fas fa-user-plus"></i> Registrar Primer Cliente
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <td class="acciones">
                                <?php if ($_SESSION['usuario_cargo'] === 'admin'): ?>
                                    <?php if ($cliente['sancionado'] == 0): ?>

                                        <button class="btn btn-success btn-sm" 
                                                onclick="eliminarSancion(<?= $cliente['id'] ?>, '<?= addslashes($cliente['nombre']) ?>')"
                                                title="Eliminar sanción">
                                            <i class="fas fa-unlock"></i> Quitar Sanción
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-warning btn-sm" 
                                                onclick="abrirModalSancion(<?= $cliente['id'] ?>, '<?= addslashes($cliente['nombre']) ?>')"
                                                title="Aplicar sanción">
                                            <i class="fas fa-lock"></i> Sancionar
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <button class="btn btn-primary btn-sm" onclick="editarCliente(<?= $cliente['id'] ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (esAdministrador() || esEmpleado()): ?>
    <div id="modalCliente" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Nuevo Cliente</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="formCliente">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="dni"><i class="fas fa-id-card"></i> DNI:</label>
                            <input type="text" id="dni" name="dni" required class="form-control" 
                                   maxlength="9" placeholder="12345678A"
                                   pattern="[0-9]{8}[A-Za-z]" title="8 números seguidos de 1 letra">
                        </div>
                        <div class="form-group">
                            <label for="nombre"><i class="fas fa-user"></i> Nombre completo:</label>
                            <input type="text" id="nombre" name="nombre" required class="form-control" 
                                   placeholder="Juan Pérez López" minlength="3">
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="cliente@ejemplo.com">
                        </div>
                        <div class="form-group">
                            <label for="telefono"><i class="fas fa-phone"></i> Teléfono:</label>
                            <input type="tel" id="telefono" name="telefono" class="form-control" 
                                   placeholder="600123456" pattern="[0-9]{9}" title="9 dígitos">
                        </div>
                        <div class="form-group full-width">
                            <label for="direccion"><i class="fas fa-home"></i> Dirección:</label>
                            <textarea id="direccion" name="direccion" rows="3" class="form-control" 
                                      placeholder="Calle Principal, 123 - Ciudad"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
                        <button type="submit" name="crear_cliente" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        <?php if (esAdministrador() || esEmpleado()): ?>
        const modal = document.getElementById('modalCliente');
        const btnNuevo = document.getElementById('btnNuevoCliente');
        const btnNuevo2 = document.getElementById('btnNuevoCliente2');
        const spanClose = document.querySelector('.close');
        const btnCancelar = document.getElementById('btnCancelar');
        const formCliente = document.getElementById('formCliente');

        function openModal() {
            modal.style.display = 'block';
            document.getElementById('dni').focus();
        }

        if (btnNuevo) btnNuevo.onclick = openModal;
        if (btnNuevo2) btnNuevo2.onclick = openModal;

        function closeModal() {
            modal.style.display = 'none';
            if (formCliente) formCliente.reset();
        }

        if (spanClose) spanClose.onclick = closeModal;
        if (btnCancelar) btnCancelar.onclick = closeModal;

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
        <?php endif; ?>

        function verDetallesCliente(id) {
            alert('Ver detalles del cliente con ID: ' + id + ' (funcionalidad no implementada)');
        }

        function editarCliente(id) {
            <?php if (esAdministrador() || esEmpleado()): ?>
            alert('Editar cliente con ID: ' + id + ' (funcionalidad no implementada)');
            <?php else: ?>
            alert('No tiene permisos para editar clientes');
            <?php endif; ?>
        }

        function confirmarEliminarCliente(id, nombre, prestamosActivos) {
            <?php if (esAdministrador()): ?>
            if (prestamosActivos > 0) {
                alert('No se puede eliminar el cliente "' + nombre + '" porque tiene ' + prestamosActivos + ' préstamo(s) activo(s).');
                return;
            }
            
            if (confirm('¿Está seguro de eliminar al cliente "' + nombre + '"? Esta acción no se puede deshacer.')) {
                // Crear un formulario dinámico para enviar la solicitud de eliminación
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id_cliente';
                input.value = id;
                
                const input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'eliminar_cliente';
                input2.value = '1';
                
                form.appendChild(input);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
            <?php else: ?>
            alert('Solo el administrador puede eliminar clientes');
            <?php endif; ?>
        }

        <?php if (esAdministrador() || esEmpleado()): ?>
        // Validación del formulario
        if (formCliente) {
            formCliente.onsubmit = function(e) {
                const dni = document.getElementById('dni').value.trim();
                const nombre = document.getElementById('nombre').value.trim();
                const email = document.getElementById('email').value.trim();
                const telefono = document.getElementById('telefono').value.trim();
                
                // Validar DNI (formato español)
                const dniRegex = /^[0-9]{8}[A-Za-z]$/;
                if (!dniRegex.test(dni)) {
                    e.preventDefault();
                    alert('El DNI debe tener 8 números seguidos de 1 letra (ej: 12345678A)');
                    return false;
                }
                
                if (nombre.length < 3) {
                    e.preventDefault();
                    alert('El nombre debe tener al menos 3 caracteres');
                    return false;
                }
                
                if (email && !isValidEmail(email)) {
                    e.preventDefault();
                    alert('Por favor, ingrese un email válido');
                    return false;
                }
                
                if (telefono && !/^[0-9]{9}$/.test(telefono)) {
                    e.preventDefault();
                    alert('El teléfono debe tener 9 dígitos');
                    return false;
                }
                
                return true;
            };
            
            function isValidEmail(email) {
                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            }
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
            grid-template-columns: repeat(2, 1fr);
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
            color: #752118;
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
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge-danger {
            background: var(--danger);
        }
        
        .badge-success {
            background: var(--success);
        }
        
        .badge-warning {
            background: var(--warning);
        }
        
        .btn-group {
            display: flex;
            gap: 6px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
        }
        
        .empty-state i {
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-muted {
            color: #94a3b8;
            font-style: italic;
        }
        
        .text-warning {
            color: var(--warning);
        }
        
        .contact-info div {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .contact-info i {
            width: 16px;
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
        
        .table-responsive {
            overflow-x: auto;
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
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</body>
</html>
<?php $conn->close(); ?>