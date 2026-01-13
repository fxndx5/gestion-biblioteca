<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../api/login.php'); 
    exit();
}

require_once '../includes/conexion.php';

function esAdministrador() {
    return isset($_SESSION['usuario_cargo']) && $_SESSION['usuario_cargo'] === 'admin';
}

function esEmpleado() {
    return isset($_SESSION['usuario_cargo']) && $_SESSION['usuario_cargo'] === 'empleado';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear categoría (solo admin)
    if (isset($_POST['crear_categoria'])) {
        if (!esAdministrador()) {
            $_SESSION['error_message'] = 'Solo el administrador puede crear categorías';
            header('Location: categorias.php');
            exit();
        }
        
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);

        // Validar nombre único
        $sql_check = "SELECT id FROM categorias WHERE nombre = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $nombre);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $_SESSION['error_message'] = 'Ya existe una categoría con este nombre';
            $stmt_check->close();
            header('Location: categorias.php');
            exit();
        }
        $stmt_check->close();

        $sql = "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nombre, $descripcion);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Categoría creada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al crear la categoría: ' . $conn->error;
        }
        
        $stmt->close();
        header('Location: categorias.php');
        exit();
    }
    
    // Eliminar categoría (solo admin, solo si no tiene libros)
    if (isset($_POST['eliminar_categoria'])) {
        if (!esAdministrador()) {
            $_SESSION['error_message'] = 'Solo el administrador puede eliminar categorías';
            header('Location: categorias.php');
            exit();
        }
        
        $id_categoria = $_POST['id_categoria'];
        
        // Verificar si la categoría tiene libros asignados
        $sql_check_libros = "SELECT COUNT(*) as total FROM libros WHERE id_categoria = ?";
        $stmt_check = $conn->prepare($sql_check_libros);
        $stmt_check->bind_param("i", $id_categoria);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $libros = $result_check->fetch_assoc();
        $stmt_check->close();
        
        if ($libros['total'] > 0) {
            $_SESSION['error_message'] = 'No se puede eliminar la categoría porque tiene ' . $libros['total'] . ' libro(s) asignado(s)';
            header('Location: categorias.php');
            exit();
        }
        
        $sql = "DELETE FROM categorias WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_categoria);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Categoría eliminada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar la categoría: ' . $conn->error;
        }
        
        $stmt->close();
        header('Location: categorias.php');
        exit();
    }
}

$sql_categorias = "SELECT c.*, 
                   (SELECT COUNT(*) FROM libros WHERE id_categoria = c.id) as total_libros
                   FROM categorias c 
                   ORDER BY c.nombre";
$result_categorias = $conn->query($sql_categorias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - Biblioteca</title>
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
                    <li>
                        <a href="clientes.php">
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li class="active">
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
                <h1>Gestión de Categorías</h1>
                <div class="header-actions">
                    <?php if (esAdministrador()): ?>
                    <button id="btnNuevaCategoria" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
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

            <div class="section">
                <h2><i class="fas fa-list"></i> Listado de Categorías</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Libros</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_categorias && $result_categorias->num_rows > 0): ?>
                                <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge"><?php echo $categoria['id']; ?></span></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($categoria['nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($categoria['descripcion']): ?>
                                            <?php echo htmlspecialchars($categoria['descripcion']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin descripción</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($categoria['total_libros'] > 0): ?>
                                            <span class="badge badge-primary">
                                                <i class="fas fa-book"></i> <?php echo $categoria['total_libros']; ?> libro(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-book"></i> Sin libros
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" onclick="verLibrosCategoria(<?php echo $categoria['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (esAdministrador()): ?>
                                            <button class="btn btn-sm btn-warning" onclick="editarCategoria(<?php echo $categoria['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmarEliminarCategoria(<?php echo $categoria['id']; ?>, '<?php echo htmlspecialchars(addslashes($categoria['nombre'])); ?>', <?php echo $categoria['total_libros']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-tags fa-3x"></i>
                                            <p>No hay categorías registradas</p>
                                            <?php if (esAdministrador()): ?>
                                            <button id="btnNuevaCategoria2" class="btn btn-primary mt-3">
                                                <i class="fas fa-plus"></i> Crear Primera Categoría
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

    <?php if (esAdministrador()): ?>
    <div id="modalCategoria" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-tag"></i> Nueva Categoría</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="formCategoria">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre"><i class="fas fa-font"></i> Nombre:</label>
                            <input type="text" id="nombre" name="nombre" required class="form-control" 
                                   placeholder="Nombre de la categoría" minlength="3" maxlength="50">
                        </div>
                        <div class="form-group full-width">
                            <label for="descripcion"><i class="fas fa-align-left"></i> Descripción:</label>
                            <textarea id="descripcion" name="descripcion" rows="4" class="form-control" 
                                      placeholder="Descripción de la categoría (opcional)" maxlength="255"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
                        <button type="submit" name="crear_categoria" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        <?php if (esAdministrador()): ?>
        const modal = document.getElementById('modalCategoria');
        const btnNuevo = document.getElementById('btnNuevaCategoria');
        const btnNuevo2 = document.getElementById('btnNuevaCategoria2');
        const spanClose = document.querySelector('.close');
        const btnCancelar = document.getElementById('btnCancelar');
        const formCategoria = document.getElementById('formCategoria');

        function openModal() {
            modal.style.display = 'block';
            document.getElementById('nombre').focus();
        }

        if (btnNuevo) btnNuevo.onclick = openModal;
        if (btnNuevo2) btnNuevo2.onclick = openModal;

        function closeModal() {
            modal.style.display = 'none';
            if (formCategoria) formCategoria.reset();
        }

        if (spanClose) spanClose.onclick = closeModal;
        if (btnCancelar) btnCancelar.onclick = closeModal;

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
        <?php endif; ?>

        function verLibrosCategoria(id) {
            alert('Ver libros de la categoría con ID: ' + id + ' (funcionalidad no implementada)');
        }

        function editarCategoria(id) {
            <?php if (esAdministrador()): ?>
            alert('Editar categoría con ID: ' + id + ' (funcionalidad no implementada)');
            <?php else: ?>
            alert('Solo el administrador puede editar categorías');
            <?php endif; ?>
        }

        function confirmarEliminarCategoria(id, nombre, totalLibros) {
            <?php if (esAdministrador()): ?>
            if (totalLibros > 0) {
                alert('No se puede eliminar la categoría "' + nombre + '" porque tiene ' + totalLibros + ' libro(s) asignado(s).\n\nPor favor, reasigne los libros a otra categoría antes de eliminar.');
                return;
            }
            
            if (confirm('¿Está seguro de eliminar la categoría "' + nombre + '"? Esta acción no se puede deshacer.')) {
                // Crear un formulario dinámico para enviar la solicitud de eliminación
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id_categoria';
                input.value = id;
                
                const input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'eliminar_categoria';
                input2.value = '1';
                
                form.appendChild(input);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
            <?php else: ?>
            alert('Solo el administrador puede eliminar categorías');
            <?php endif; ?>
        }

        <?php if (esAdministrador()): ?>
        // Validación del formulario
        if (formCategoria) {
            formCategoria.onsubmit = function(e) {
                const nombre = document.getElementById('nombre').value.trim();
                const descripcion = document.getElementById('descripcion').value.trim();
                
                if (nombre.length < 3) {
                    e.preventDefault();
                    alert('El nombre debe tener al menos 3 caracteres');
                    return false;
                }
                
                if (nombre.length > 50) {
                    e.preventDefault();
                    alert('El nombre no puede tener más de 50 caracteres');
                    return false;
                }
                
                if (descripcion.length > 255) {
                    e.preventDefault();
                    alert('La descripción no puede tener más de 255 caracteres');
                    return false;
                }
                
                return true;
            };
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
            max-width: 600px;
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
        
        .badge-primary {
            background: var(--primary-blue);
        }
        
        .badge-secondary {
            background: #94a3b8;
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
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</body>
</html>
<?php $conn->close(); ?>