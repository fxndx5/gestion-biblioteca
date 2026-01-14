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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_libro'])) {
    // Verificar permiso
    if (!esAdministrador() && $_SESSION['usuario_cargo'] !== 'empleado') {
        $_SESSION['error_message'] = 'No tiene permisos para crear libros';
        header('Location: libros.php');
        exit();
    }
    
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $isbn = $_POST['isbn'];
    $id_categoria = $_POST['id_categoria'];
    $editorial = $_POST['editorial'];
    $anio_publicacion = $_POST['anio_publicacion'];
    $ejemplares = $_POST['ejemplares'];
    $ubicacion = $_POST['ubicacion'];

    $sql = "INSERT INTO libros (titulo, autor, isbn, id_categoria, editorial, año_publicacion, ejemplares, disponibles, ubicacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisiiis", $titulo, $autor, $isbn, $id_categoria, $editorial, $anio_publicacion, $ejemplares, $ejemplares, $ubicacion);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Libro creado exitosamente';
    } else {
        $_SESSION['error_message'] = 'Error al crear el libro: ' . $conn->error;
    }
    
    $stmt->close();
    header('Location: libros.php');
    exit();
}

$sql_libros = "SELECT l.*, c.nombre as categoria_nombre 
               FROM libros l 
               LEFT JOIN categorias c ON l.id_categoria = c.id 
               ORDER BY l.titulo";
$result_libros = $conn->query($sql_libros);

$sql_categorias = "SELECT * FROM categorias ORDER BY nombre";
$result_categorias = $conn->query($sql_categorias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Libros - Biblioteca</title>
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
                    <li class="active">
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
                <h1> Gestión de Libros</h1>
                <div class="header-actions">
                    <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
                    <button id="btnNuevoLibro" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Libro
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
                <h2><i class="fas fa-list"></i> Catálogo de Libros</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>ISBN</th>
                                <th>Categoría</th>
                                <th>Disponibles</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_libros && $result_libros->num_rows > 0): ?>
                                <?php while ($libro = $result_libros->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge"><?php echo $libro['id']; ?></span></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong>
                                        <?php if ($libro['editorial']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($libro['editorial']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                                    <td><code><?php echo htmlspecialchars($libro['isbn']); ?></code></td>
                                    <td>
                                        <?php if ($libro['categoria_nombre']): ?>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($libro['categoria_nombre']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin categoría</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="stock-info">
                                            <span class="stock-number"><?php echo $libro['disponibles']; ?></span>
                                            <span class="stock-total">/ <?php echo $libro['ejemplares']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($libro['disponibles'] > 0): ?>
                                            <span class="status activo"><i class="fas fa-check"></i> Disponible</span>
                                        <?php else: ?>
                                            <span class="status atrasado"><i class="fas fa-times"></i> Agotado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" onclick="verDetalles(<?php echo $libro['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
                                            <button class="btn btn-sm btn-warning" onclick="editarLibro(<?php echo $libro['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if (esAdministrador()): ?>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarLibro(<?php echo $libro['id']; ?>)">
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
                                            <i class="fas fa-book fa-3x"></i>
                                            <p>No hay libros registrados</p>
                                            <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
                                            <button id="btnNuevoLibro2" class="btn btn-primary mt-3">
                                                <i class="fas fa-plus"></i> Agregar Primer Libro
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
    <div id="modalLibro" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-book-medical"></i> Nuevo Libro</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="formLibro">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="titulo"><i class="fas fa-heading"></i> Título:</label>
                            <input type="text" id="titulo" name="titulo" required class="form-control" placeholder="Título del libro">
                        </div>
                        <div class="form-group">
                            <label for="autor"><i class="fas fa-user-pen"></i> Autor:</label>
                            <input type="text" id="autor" name="autor" required class="form-control" placeholder="Autor del libro">
                        </div>
                        <div class="form-group">
                            <label for="isbn"><i class="fas fa-barcode"></i> ISBN:</label>
                            <input type="text" id="isbn" name="isbn" required class="form-control" placeholder="ISBN-13">
                        </div>
                        <div class="form-group">
                            <label for="id_categoria"><i class="fas fa-tag"></i> Categoría:</label>
                            <select id="id_categoria" name="id_categoria" class="form-control">
                                <option value="">Seleccione categoría</option>
                                <?php if ($result_categorias && $result_categorias->num_rows > 0): 
                                    $result_categorias->data_seek(0); // Reset pointer
                                    while ($categoria = $result_categorias->fetch_assoc()): ?>
                                        <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editorial"><i class="fas fa-building"></i> Editorial:</label>
                            <input type="text" id="editorial" name="editorial" class="form-control" placeholder="Nombre editorial">
                        </div>
                        <div class="form-group">
                            <label for="anio_publicacion"><i class="fas fa-calendar"></i> Año publicación:</label>
                            <input type="number" id="anio_publicacion" name="anio_publicacion" 
                                   min="1000" max="<?php echo date('Y'); ?>" 
                                   class="form-control" placeholder="<?php echo date('Y'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="ejemplares"><i class="fas fa-copy"></i> Ejemplares:</label>
                            <input type="number" id="ejemplares" name="ejemplares" min="1" value="1" class="form-control">
                        </div>
                        <div class="form-group full-width">
                            <label for="ubicacion"><i class="fas fa-map-marker-alt"></i> Ubicación:</label>
                            <input type="text" id="ubicacion" name="ubicacion" class="form-control" placeholder="Estante 5, Sección B">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
                        <button type="submit" name="crear_libro" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Libro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
        const modal = document.getElementById('modalLibro');
        const btnNuevo = document.getElementById('btnNuevoLibro');
        const btnNuevo2 = document.getElementById('btnNuevoLibro2');
        const spanClose = document.querySelector('.close');
        const btnCancelar = document.getElementById('btnCancelar');
        const formLibro = document.getElementById('formLibro');

        function openModal() {
            modal.style.display = 'block';
            document.getElementById('titulo').focus();
        }

        if (btnNuevo) btnNuevo.onclick = openModal;
        if (btnNuevo2) btnNuevo2.onclick = openModal;

        function closeModal() {
            modal.style.display = 'none';
            formLibro.reset();
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
            alert('Ver detalles del libro con ID: ' + id + ' (funcionalidad no implementada)');
        }

        function editarLibro(id) {
            <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
            alert('Editar libro con ID: ' + id + ' (funcionalidad no implementada)');
            <?php else: ?>
            alert('No tiene permisos para editar libros');
            <?php endif; ?>
        }

        function eliminarLibro(id) {
            <?php if (esAdministrador()): ?>
            if (confirm('¿Está seguro de eliminar este libro y todos sus préstamos relacionados?')) {
                alert('Eliminar libro con ID: ' + id + ' (funcionalidad no implementada)');
            }
            <?php else: ?>
            alert('Solo el administrador puede eliminar libros');
            <?php endif; ?>
        }

        <?php if (esAdministrador() || $_SESSION['usuario_cargo'] === 'empleado'): ?>
        // Validación del formulario
        if (formLibro) {
            formLibro.onsubmit = function(e) {
                const titulo = document.getElementById('titulo').value.trim();
                const autor = document.getElementById('autor').value.trim();
                const isbn = document.getElementById('isbn').value.trim();
                const ejemplares = document.getElementById('ejemplares').value;
                
                if (!titulo) {
                    e.preventDefault();
                    alert('Por favor, ingrese el título del libro');
                    return false;
                }
                
                if (!autor) {
                    e.preventDefault();
                    alert('Por favor, ingrese el autor del libro');
                    return false;
                }
                
                if (!isbn) {
                    e.preventDefault();
                    alert('Por favor, ingrese el ISBN del libro');
                    return false;
                }
                
                if (ejemplares < 1) {
                    e.preventDefault();
                    alert('Debe haber al menos 1 ejemplar');
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
            max-width: 800px;
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge-info {
            background: var(--primary-blue);
        }
        
        .stock-info {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .stock-number {
            font-weight: bold;
            font-size: 18px;
            color: var(--dark-blue);
        }
        
        .stock-total {
            color: #94a3b8;
            font-size: 14px;
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