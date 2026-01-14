<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: api/login.php');
    exit();
}

require_once 'includes/conexion.php';

function esAdministrador() {
    return isset($_SESSION['usuario_cargo']) && $_SESSION['usuario_cargo'] === 'admin';
}

function esEmpleado() {
    return isset($_SESSION['usuario_cargo']) && $_SESSION['usuario_cargo'] === 'empleado';
}

$estadisticas_avanzadas = [];
$categorias_stats = [];
$clientes_stats = [];
$libros_stats = [];
$top_libros = [];

// SOLO para admin: obtener estadísticas avanzadas
if (esAdministrador()) {
    // contadores de clientes sancionados y activos
    $sql_clientes = "SELECT 
        COUNT(*) as total_clientes,
        SUM(CASE WHEN sancionado = 1 THEN 1 ELSE 0 END) as clientes_sancionados,
        SUM(CASE WHEN sancionado = 0 THEN 1 ELSE 0 END) as clientes_activos
        FROM clientes";
    
    $result = $conn->query($sql_clientes);
    if ($result) {
        $clientes_stats = $result->fetch_assoc();
    }
    
    // disponibles vs alquilados
    $sql_libros = "SELECT 
        COUNT(*) as total_libros,
        SUM(disponibles) as libros_disponibles,
        SUM(ejemplares - disponibles) as libros_prestados,
        SUM(ejemplares) as total_ejemplares
        FROM libros";
    
    $result = $conn->query($sql_libros);
    if ($result) {
        $libros_stats = $result->fetch_assoc();
    }
    
    // libros por categoría con detalles
    $sql_categorias = "SELECT 
        c.id,
        c.nombre as categoria,
        COUNT(l.id) as cantidad_libros,
        SUM(l.ejemplares) as total_ejemplares,
        SUM(l.disponibles) as disponibles,
        SUM(l.ejemplares - l.disponibles) as prestados
        FROM categorias c
        LEFT JOIN libros l ON c.id = l.id_categoria
        GROUP BY c.id
        ORDER BY cantidad_libros DESC";
    
    $result = $conn->query($sql_categorias);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categorias_stats[] = $row;
        }
    }
    
    // prestamo por estado
    $sql_prestamos_estado = "SELECT 
        COUNT(*) as total_prestamos,
        SUM(CASE WHEN fecha_devolucion_real IS NOT NULL THEN 1 ELSE 0 END) as devueltos,
        SUM(CASE WHEN fecha_devolucion_real IS NULL AND CURDATE() <= fecha_devolucion_estimada THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN fecha_devolucion_real IS NULL AND CURDATE() > fecha_devolucion_estimada THEN 1 ELSE 0 END) as atrasados
        FROM prestamos";
    
    $result = $conn->query($sql_prestamos_estado);
    if ($result) {
        $estadisticas_avanzadas['prestamos'] = $result->fetch_assoc();
    }
    
    // Top 5 libros mas prestados
    $sql_top_libros = "SELECT 
        l.titulo,
        l.autor,
        COUNT(p.id) as veces_prestado
        FROM libros l
        LEFT JOIN prestamos p ON l.id = p.id_libro
        GROUP BY l.id
        ORDER BY veces_prestado DESC
        LIMIT 5";
    
    $result = $conn->query($sql_top_libros);
    $top_libros = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $top_libros[] = $row;
        }
    }
    
    // empleados activos
    $sql_empleados = "SELECT 
        COUNT(*) as total_empleados,
        SUM(CASE WHEN cargo = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN cargo = 'empleado' THEN 1 ELSE 0 END) as empleados
        FROM empleados";
    
    $result = $conn->query($sql_empleados);
    if ($result) {
        $estadisticas_avanzadas['empleados'] = $result->fetch_assoc();
    }
}

// Para empleados (NO admin): obtener solo estadísticas básicas
if (!esAdministrador()) {
    $sql_libros = "SELECT COUNT(*) as total FROM libros";
    $result = $conn->query($sql_libros);
    $total_libros = $result ? $result->fetch_assoc()['total'] : 0;
    
    $sql_prestamos_activos = "SELECT COUNT(*) as activos FROM prestamos WHERE fecha_devolucion_real IS NULL AND CURDATE() <= fecha_devolucion_estimada";
    $result = $conn->query($sql_prestamos_activos);
    $prestamos_activos = $result ? $result->fetch_assoc()['activos'] : 0;
    
    $sql_clientes = "SELECT COUNT(*) as total FROM clientes";
    $result = $conn->query($sql_clientes);
    $total_clientes = $result ? $result->fetch_assoc()['total'] : 0;
    
    $sql_prestamos_atrasados = "SELECT COUNT(*) as atrasados FROM prestamos WHERE fecha_devolucion_real IS NULL AND CURDATE() > fecha_devolucion_estimada";
    $result = $conn->query($sql_prestamos_atrasados);
    $prestamos_atrasados = $result ? $result->fetch_assoc()['atrasados'] : 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Biblioteca</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>

function abrirModalEliminarSancion() {
    console.log('Abriendo modal...');
    const modal = document.getElementById('modalEliminarSancion');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const input = document.getElementById('buscarSancion');
            if (input) input.focus();
        }, 100);
    } else {
        alert('Modal no encontrado. Recarga la página.');
    }
}

function buscarSancionActiva(texto) {
    if (texto.length < 2) {
        document.getElementById('resultadosSancion').innerHTML = '';
        return;
    }
    
    fetch(`api/buscar_cliente.php?q=${encodeURIComponent(texto)}`)
        .then(response => response.json())
        .then(clientes => {
            const container = document.getElementById('resultadosSancion');
            const sancionados = clientes.filter(c => c.sancionado == 0);
            
            if (sancionados.length === 0) {
                container.innerHTML = '<p class="text-muted" style="padding: 10px;">No se encontraron clientes sancionados</p>';
                return;
            }
            
            container.innerHTML = sancionados.map(cliente => `
                <div class="search-result-item" onclick="seleccionarSancion(${cliente.id}, '${cliente.nombre.replace(/'/g, "\\'")}', '${cliente.dni}')">
                    <div class="result-info">
                        <strong>${cliente.nombre}</strong>
                        <small>DNI: ${cliente.dni}</small><br>
                        <span class="badge badge-danger">Sancionado</span>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => console.error('Error:', error));
}

function seleccionarSancion(id, nombre, dni) {
    document.getElementById('sancionSeleccionada').value = id;
    document.getElementById('buscarSancion').value = `${nombre} (${dni})`;
    document.getElementById('resultadosSancion').innerHTML = '';
}

function eliminarSancion() {
    const idCliente = document.getElementById('sancionSeleccionada').value;
    const razon = document.getElementById('razonEliminacion').value;
    
    if (!idCliente) {
        alert('Por favor, selecciona un cliente');
        return;
    }
    
    if (!razon.trim()) {
        alert('Por favor, indica el motivo de la reactivación');
        return;
    }
    
    if (!confirm('¿Estás seguro de querer reactivar este cliente?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id_cliente', idCliente);
    
    fetch('api/eliminar_sancion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('modalEliminarSancion').classList.remove('show');
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la sanción');
    });
}
</script>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-brand">
                    <img src="images/logo.png" class="logo-image" alt="Logo Biblioteca">
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
                    <li class="active">
                        <a href="dashboard.php">
                            
                            <span>Inicio</span>
                        </a>
                    </li>
                    <li>
                        <a href="modulos/libros.php">
                            <span>Libros</span>
                        </a>
                    </li>
                    <li>
                        <a href="modulos/prestamos.php">
                            <span>Préstamos</span>
                        </a>
                    </li>
                    <li>
                        <a href="modulos/clientes.php">
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="modulos/categorias.php">
                            <span>Categorías</span>
                        </a>
                    </li>
                    <li>
                        <a href="modulos/logout.php">
                            <span>Salir</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <header class="dashboard-header">
                <h1>Panel de Control 
                    <?php if (esAdministrador()): ?>
                        <small class="admin-badge">Vista Administrador</small>
                    <?php endif; ?>
                </h1>
                <div class="header-actions">
                    <span class="current-date-time">
                        <i class="far fa-calendar"></i>
                        <span id="currentDateTime"><?php echo date('d/m/Y H:i:s'); ?></span>
                    </span>
                </div>
            </header>
            
            <!-- SECCIÓN EXCLUSIVA PARA ADMINISTRADORES -->
            <?php if (esAdministrador()): ?>
            <div class="admin-section">
                <h2>Estadísticas Avanzadas (Solo Administrador)</h2>
                
                <!-- BOTONES SOLO PARA ADMIN -->
                <div class="prestamos-actions" style="margin-bottom: 30px;">
                    <button class="btn btn-primary" onclick="abrirModalNuevoPrestamo()">
                        Nuevo Préstamo
                    </button>
                    <button class="btn btn-success" onclick="abrirModalDevolucion()">
                        Registrar Devolución
                    </button>
                    <button class="btn btn-warning" onclick="abrirModalEliminarSancion()">
                        Eliminar Sanción
                    </button>
                </div>
                
                <!-- Estadisticas detalladas de clientes -->
                <div class="stats-grid-advanced">
                    <div class="stat-card-advanced">
                        <h4></i> Clientes</h4>
                        <div class="stat-number-advanced">
                            <?php echo $clientes_stats['total_clientes'] ?? 0; ?>
                        </div>
                        <div class="stat-detail">
                            <span class="status-active">
                                <?php echo $clientes_stats['clientes_activos'] ?? 0; ?> Activos
                            </span>
                            <span class="status-suspended">
                                <?php echo $clientes_stats['clientes_sancionados'] ?? 0; ?> Sancionados
                            </span>
                        </div>
                        <?php if (isset($clientes_stats['total_clientes']) && $clientes_stats['total_clientes'] > 0): ?>
                        <div class="category-progress">
                            <div class="category-progress-bar" style="width: <?php echo (($clientes_stats['clientes_activos'] ?? 0) / $clientes_stats['total_clientes']) * 100; ?>%"></div>
                        </div>
                        <div class="percentage">
                            <?php echo round((($clientes_stats['clientes_activos'] ?? 0) / $clientes_stats['total_clientes']) * 100, 1); ?>% Activos
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Estadísticas detalladas de libros -->
                    <div class="stat-card-advanced">
                        <h4>Libros</h4>
                        <div class="stat-number-advanced">
                            <?php echo $libros_stats['total_ejemplares'] ?? 0; ?>
                        </div>
                        <div class="stat-detail">
                            <span class="status-available">
                                <?php echo $libros_stats['libros_disponibles'] ?? 0; ?> Disponibles
                            </span>
                            <span class="status-borrowed">
                                <?php echo $libros_stats['libros_prestados'] ?? 0; ?> Prestados
                            </span>
                        </div>
                        <?php if (isset($libros_stats['total_ejemplares']) && $libros_stats['total_ejemplares'] > 0): ?>
                        <div class="category-progress">
                            <div class="category-progress-bar" style="width: <?php echo (($libros_stats['libros_disponibles'] ?? 0) / $libros_stats['total_ejemplares']) * 100; ?>%"></div>
                        </div>
                        <div class="percentage">
                            <?php echo round((($libros_stats['libros_disponibles'] ?? 0) / $libros_stats['total_ejemplares']) * 100, 1); ?>% Disponibilidad
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Estadísticas de préstamos -->
                    <div class="stat-card-advanced">
                        <h4>Préstamos</h4>
                        <div class="stat-number-advanced">
                            <?php echo $estadisticas_avanzadas['prestamos']['total_prestamos'] ?? 0; ?>
                        </div>
                        <div class="stat-detail">
                            <div>
                                <small>Activos: <?php echo $estadisticas_avanzadas['prestamos']['activos'] ?? 0; ?></small><br>
                                <small>Devueltos: <?php echo $estadisticas_avanzadas['prestamos']['devueltos'] ?? 0; ?></small>
                            </div>
                            <div>
                                <small>Atrasados: <?php echo $estadisticas_avanzadas['prestamos']['atrasados'] ?? 0; ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estadísticas de empleados -->
                    <div class="stat-card-advanced">
                        <h4>Empleados</h4>
                        <div class="stat-number-advanced">
                            <?php echo $estadisticas_avanzadas['empleados']['total_empleados'] ?? 0; ?>
                        </div>
                        <div class="stat-detail">
                            <span class="status-active">
                                <?php echo $estadisticas_avanzadas['empleados']['admins'] ?? 0; ?> Admin
                            </span>
                            <span class="status-available">
                                <?php echo $estadisticas_avanzadas['empleados']['empleados'] ?? 0; ?> Empleados
                            </span>
                        </div>
                    </div>
                </div>
                
                
                
                <!-- Top 5 libros más prestados -->
                <h3>Top 5 Libros Más Prestados</h3>
                <?php if (!empty($top_libros)): ?>
                    <ul class="top-books-list">
                        <?php foreach ($top_libros as $index => $libro): ?>
                            <li>
                                <div>
                                    <strong><?php echo ($index + 1) . '. ' . htmlspecialchars($libro['titulo']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($libro['autor']); ?></small>
                                </div>
                                <span class="book-badge">
                                    <?php echo $libro['veces_prestado']; ?> préstamos
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No hay datos de préstamos registrados</p>
                <?php endif; ?>
            </div>
            
            <?php else: ?>
            <!-- ESTADÍSTICAS BÁSICAS SOLO PARA EMPLEADOS (NO admin) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Libros</h3>
                    <p class="stat-number"><?php echo $total_libros; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Préstamos Activos</h3>
                    <p class="stat-number"><?php echo $prestamos_activos; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Clientes Registrados</h3>
                    <p class="stat-number"><?php echo $total_clientes; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Préstamos Atrasados</h3>
                    <p class="stat-number"><?php echo $prestamos_atrasados; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="section prestamos-rapidos">
            <h2>Gestión Rápida de Préstamos</h2>
            
            <?php if (!esAdministrador()): ?>
            <!-- BOTONES SOLO PARA EMPLEADOS (NO ADMIN) -->
            <div class="prestamos-actions">
                <button class="btn btn-primary" onclick="abrirModalNuevoPrestamo()">
                    Nuevo Préstamo
                </button>
                <button class="btn btn-success" onclick="abrirModalDevolucion()">
                    Registrar Devolución
                </button>
            </div>
            <?php endif; ?>

                <h3 style="margin-top: 30px;">Préstamos Activos Pendientes de Devolución</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Libro</th>
                                <th>Cliente</th>
                                <th>DNI</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Estimada Dev.</th>
                                <th>Días Restantes</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPrestamosActivos">
                            <?php
                            $sql_activos = "SELECT 
                                p.id,
                                p.fecha_prestamo,
                                p.fecha_devolucion_estimada,
                                p.estado,
                                l.titulo,
                                l.autor,
                                c.nombre,
                                c.dni,
                                DATEDIFF(p.fecha_devolucion_estimada, CURDATE()) as dias_restantes
                                FROM prestamos p
                                JOIN libros l ON p.id_libro = l.id
                                JOIN clientes c ON p.id_cliente = c.id
                                WHERE p.fecha_devolucion_real IS NULL
                                ORDER BY p.fecha_devolucion_estimada ASC
                                LIMIT 10";
                            
                            $result_activos = $conn->query($sql_activos);
                            
                            if ($result_activos && $result_activos->num_rows > 0):
                                while ($prestamo = $result_activos->fetch_assoc()):
                                    $dias = $prestamo['dias_restantes'];
                                    $clase_estado = $dias < 0 ? 'atrasado' : ($dias <= 2 ? 'warning' : 'active');
                            ?>
                            <tr>
                                <td><strong><?php echo $prestamo['id']; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($prestamo['titulo']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($prestamo['autor']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($prestamo['nombre']); ?></td>
                                <td><span class="code"><?php echo htmlspecialchars($prestamo['dni']); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_estimada'])); ?></td>
                                <td>
                                    <?php if ($dias < 0): ?>
                                        <span class="badge badge-danger">
                                            <?php echo abs($dias); ?> días atrasado
                                        </span>
                                    <?php elseif ($dias <= 2): ?>
                                        <span class="badge badge-warning">
                                            <?php echo $dias; ?> días
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><?php echo $dias; ?> días</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status <?php echo $clase_estado; ?>">
                                        <?php echo $dias < 0 ? 'Atrasado' : 'Activo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action btn-success-sm" 
                                            onclick="devolverLibro(<?php echo $prestamo['id']; ?>, '<?php echo htmlspecialchars($prestamo['titulo']); ?>', '<?php echo htmlspecialchars($prestamo['nombre']); ?>')"
                                            title="Registrar Devolución">x
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="empty-state">
                                        <p>No hay préstamos activos</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== MODAL NUEVO PRÉSTAMO ===== -->
            <div id="modalNuevoPrestamo" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3> Registrar Nuevo Préstamo</h3>
                        <button class="btn-close" onclick="cerrarModal('modalNuevoPrestamo')">&times;</button>
                    </div>
                    
                    <form id="formNuevoPrestamo" onsubmit="event.preventDefault(); registrarPrestamo();">
                        <div class="modal-body">
                            <!-- Paso 1: Buscar Cliente -->
                            <div class="form-section">
                                <h4>1. Buscar Cliente</h4>
                                <div class="form-group">
                                    <label for="buscarCliente">DNI o Nombre del Cliente:</label>
                                    <input type="text" 
                                        id="buscarCliente" 
                                        placeholder="Ej: 12345678 o Juan Pérez"
                                        oninput="buscarClientePrestamo(this.value)"
                                        required>
                                </div>
                                <div id="resultadosCliente" class="search-results"></div>
                                <input type="hidden" id="clienteSeleccionado" name="id_cliente" required>
                            </div>

                            <!-- Paso 2: Buscar Libro -->
                            <div class="form-section">
                                <h4>2. Buscar Libro</h4>
                                <div class="form-group">
                                    <label for="buscarLibro">ID, Título o Autor del Libro:</label>
                                    <input type="text" 
                                        id="buscarLibro" 
                                        placeholder="Ej: El Quijote o Miguel de Cervantes"
                                        oninput="buscarLibroPrestamo(this.value)"
                                        required>
                                </div>
                                <div id="resultadosLibro" class="search-results"></div>
                                <input type="hidden" id="libroSeleccionado" name="id_libro" required>
                            </div>

                            <!-- Paso 3: Detalles del Préstamo -->
                            <div class="form-section">
                                <h4>3. Detalles del Préstamo</h4>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="fechaDevolucion">Fecha de Devolución Estimada:</label>
                                        <input type="date" 
                                            id="fechaDevolucion" 
                                            name="fecha_devolucion_estimada"
                                            min="<?php echo date('Y-m-d'); ?>"
                                            required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="observaciones">Observaciones (Opcional):</label>
                                    <textarea id="observaciones" 
                                            name="observaciones" 
                                            rows="3"
                                            placeholder="Ej: Cliente regular, buen estado del libro, etc."></textarea>
                                </div>
                            </div>

                            <div id="alertaPrestamo" class="alert" style="display: none;"></div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalNuevoPrestamo')">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Registrar Préstamo
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ===== MODAL DEVOLUCIÓN ===== -->
            <div id="modalDevolucion" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Registrar Devolución</h3>
                        <button class="btn-close" onclick="cerrarModal('modalDevolucion')">&times;</button>
                    </div>
                    
                    <form id="formDevolucion" onsubmit="event.preventDefault(); registrarDevolucion();">
                        <div class="modal-body">
                            <div class="form-section">
                                <h4>Buscar Préstamo Activo</h4>
                                <div class="form-group">
                                    <label for="buscarPrestamo">DNI del Cliente o ID del Préstamo:</label>
                                    <input type="text" 
                                        id="buscarPrestamo" 
                                        placeholder="Ej: 12345678 o ID: 5"
                                        oninput="buscarPrestamoActivo(this.value)"
                                        required>
                                </div>
                                <div id="resultadosPrestamo" class="search-results"></div>
                                <input type="hidden" id="prestamoSeleccionado" name="id_prestamo" required>
                            </div>

                            <div class="form-group">
                                <label for="observacionesDevolucion">Observaciones de Devolución (Opcional):</label>
                                <textarea id="observacionesDevolucion" 
                                        name="observaciones_devolucion" 
                                        rows="3"
                                        placeholder="Ej: Libro en buen estado, sin daños"></textarea>
                            </div>

                            <div id="alertaDevolucion" class="alert" style="display: none;"></div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalDevolucion')">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success">
                                Confirmar Devolución
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- ===== MODAL ELIMINAR SANCIÓN ===== -->
            <?php if (esAdministrador()): ?>
            <div id="modalEliminarSancion" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3> Eliminar Sanción</h3>
                        <button class="btn-close" onclick="cerrarModal('modalEliminarSancion')">&times;</button>
                    </div>
                    
                    <form id="formEliminarSancion" onsubmit="event.preventDefault(); eliminarSancion();">
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Advertencia:</strong> Esta acción reactivará al cliente, permitiéndole realizar préstamos nuevamente.
                            </div>
                            
                            <div class="form-section">
                                <h4>Buscar Cliente Sancionado</h4>
                                <div class="form-group">
                                    <label for="buscarSancion">DNI o Nombre del Cliente:</label>
                                    <input type="text" 
                                        id="buscarSancion" 
                                        placeholder="Ej: 12345678 o Juan Pérez"
                                        oninput="buscarSancionActiva(this.value)"
                                        required>
                                </div>
                                <div id="resultadosSancion" class="search-results"></div>
                                <input type="hidden" id="sancionSeleccionada" name="id_cliente" required>
                            </div>

                            <div id="infoSancion" class="sancion-info" style="display: none;">
                                <div class="info-card">
                                    <h4><i class="fas fa-info-circle"></i> Información del Cliente</h4>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="info-label">Cliente:</span>
                                            <span id="infoCliente" class="info-value"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">DNI:</span>
                                            <span id="infoDNI" class="info-value"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Email:</span>
                                            <span id="infoEmail" class="info-value"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Teléfono:</span>
                                            <span id="infoTelefono" class="info-value"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Fecha Registro:</span>
                                            <span id="infoFechaRegistro" class="info-value"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Fin de Sanción:</span>
                                            <span id="infoFechaFin" class="info-value"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Estado Sanción:</span>
                                            <span id="infoEstado" class="info-value"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Días Restantes:</span>
                                            <span id="infoDiasRestantes" class="info-value"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="razonEliminacion">Motivo de Reactivación:</label>
                                <textarea id="razonEliminacion" 
                                        name="razon_eliminacion" 
                                        rows="3"
                                        placeholder="Ej: Sanción completada, error en el registro, cliente ha pagado multa, etc."
                                        required></textarea>
                            </div>

                            <div id="alertaEliminarSancion" class="alert" style="display: none;"></div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalEliminarSancion')">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Reactivar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <style>
            /* Estilos específicos para la gestión de préstamos */
            .prestamos-rapidos {
                background: linear-gradient(135deg, #667eea15, #764ba215);
            }

            .prestamos-actions {
                display: flex;
                gap: 15px;
                margin-bottom: 25px;
                flex-wrap: wrap;
            }

            .btn-action {
                padding: 8px 12px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.3s;
                font-size: 0.9rem;
            }

            .btn-success-sm {
                background: var(--success);
                color: white;
            }

            .btn-success-sm:hover {
                background: #27ae60;
                transform: scale(1.1);
            }

            .modal {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                animation: fadeIn 0.3s;
            }

            .modal.show {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .modal-content {
                background: white;
                border-radius: 16px;
                width: 90%;
                max-width: 700px;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                animation: slideInUp 0.3s;
            }

            .modal-header {
                padding: 25px 30px;
                border-bottom: 2px solid var(--border-color);
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: var(--dark-blue);
                color: white;
                border-radius: 16px 16px 0 0;
            }

            .modal-header h3 {
                margin: 0;
                font-size: 1.3rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .btn-close {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                font-size: 28px;
                cursor: pointer;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s;
            }

            .btn-close:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: rotate(90deg);
            }

            .modal-body {
                padding: 30px;
            }

            .modal-footer {
                padding: 20px 30px;
                border-top: 1px solid var(--border-color);
                display: flex;
                justify-content: flex-end;
                gap: 15px;
                background: var(--bg-light);
                border-radius: 0 0 16px 16px;
            }

            .form-section {
                margin-bottom: 30px;
                padding: 20px;
                background: var(--bg-light);
                border-radius: 12px;
                border-left: 4px solid var(--primary-blue);
            }

            .form-section h4 {
                color: var(--text-dark);
                margin-bottom: 15px;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .search-results {
                margin-top: 15px;
                max-height: 250px;
                overflow-y: auto;
                border-radius: 8px;
            }

            .search-result-item {
                padding: 15px;
                background: white;
                border: 2px solid var(--border-color);
                border-radius: 8px;
                margin-bottom: 10px;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .search-result-item:hover {
                border-color: var(--primary-blue);
                background: rgba(52, 152, 219, 0.05);
                transform: translateX(5px);
            }

            .search-result-item.selected {
                border-color: var(--success);
                background: rgba(46, 204, 113, 0.1);
            }

            .result-info {
                flex: 1;
            }

            .result-info strong {
                display: block;
                color: var(--text-dark);
                margin-bottom: 5px;
            }

            .result-info small {
                color: var(--text-muted);
            }

            .result-badge {
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: 600;
            }

            .disponible {
                background: #d4edda;
                color: #155724;
            }

            .no-disponible {
                background: #f8d7da;
                color: #721c24;
            }

            @keyframes slideInUp {
                from {
                    transform: translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            </style>

            <script>
            // ===== FUNCIONES PARA GESTIÓN DE PRÉSTAMOS =====

            // Abrir/Cerrar Modales
            function abrirModalNuevoPrestamo() {
                document.getElementById('modalNuevoPrestamo').classList.add('show');
                document.getElementById('buscarCliente').focus();
                
                // Establecer fecha mínima (hoy) y fecha por defecto (15 días)
                const hoy = new Date();
                const fechaMin = hoy.toISOString().split('T')[0];
                const fechaDefault = new Date(hoy.setDate(hoy.getDate() + 15)).toISOString().split('T')[0];
                
                document.getElementById('fechaDevolucion').min = fechaMin;
                document.getElementById('fechaDevolucion').value = fechaDefault;
            }

            function abrirModalDevolucion() {
                document.getElementById('modalDevolucion').classList.add('show');
                document.getElementById('buscarPrestamo').focus();
            }

            function cerrarModal(modalId) {
                document.getElementById(modalId).classList.remove('show');
                
                // Limpiar formularios
                if (modalId === 'modalNuevoPrestamo') {
                    document.getElementById('formNuevoPrestamo').reset();
                    document.getElementById('resultadosCliente').innerHTML = '';
                    document.getElementById('resultadosLibro').innerHTML = '';
                } else {
                    document.getElementById('formDevolucion').reset();
                    document.getElementById('resultadosPrestamo').innerHTML = '';
                }
            }

            // Cerrar modal al hacer clic fuera
            window.onclick = function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.classList.remove('show');
                }
            }

            // Buscar Cliente
            let timeoutCliente;
            function buscarClientePrestamo(texto) {
                clearTimeout(timeoutCliente);
                
                if (texto.length < 2) {
                    document.getElementById('resultadosCliente').innerHTML = '';
                    return;
                }
                
                timeoutCliente = setTimeout(async () => {
                    try {
                        const response = await fetch(`api/buscar_cliente.php?q=${encodeURIComponent(texto)}`);
                        const clientes = await response.json();
                        
                        mostrarResultadosCliente(clientes);
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }, 300);
            }

            function mostrarResultadosCliente(clientes) {
                const container = document.getElementById('resultadosCliente');
                
                if (clientes.length === 0) {
                    container.innerHTML = '<p class="text-muted" style="padding: 10px;">No se encontraron clientes</p>';
                    return;
                }
                
                container.innerHTML = clientes.map(cliente => `
                    <div class="search-result-item" onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre}', '${cliente.dni}')">
                        <div class="result-info">
                            <strong>${cliente.nombre}</strong>
                            <small>DNI: ${cliente.dni}</small>
                            ${cliente.sancionado == 0 ? '<br><span class="badge badge-danger">Sancionado</span>' : ''}
                        </div>
                    </div>
                `).join('');
            }

            function seleccionarCliente(id, nombre, dni) {
                document.getElementById('clienteSeleccionado').value = id;
                document.getElementById('buscarCliente').value = `${nombre} (${dni})`;
                document.getElementById('resultadosCliente').innerHTML = '';
            }

            // Buscar Libro
            let timeoutLibro;
            function buscarLibroPrestamo(texto) {
                clearTimeout(timeoutLibro);
                
                if (texto.length < 2) {
                    document.getElementById('resultadosLibro').innerHTML = '';
                    return;
                }
                
                timeoutLibro = setTimeout(async () => {
                    try {
                        const response = await fetch(`api/buscar_libro.php?q=${encodeURIComponent(texto)}`);
                        const libros = await response.json();
                        
                        mostrarResultadosLibro(libros);
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }, 300);
            }

            function mostrarResultadosLibro(libros) {
                const container = document.getElementById('resultadosLibro');
                
                if (libros.length === 0) {
                    container.innerHTML = '<p class="text-muted" style="padding: 10px;">No se encontraron libros</p>';
                    return;
                }
                
                container.innerHTML = libros.map(libro => `
                    <div class="search-result-item ${libro.disponibles > 0 ? '' : 'disabled'}" 
                        onclick="${libro.disponibles > 0 ? `seleccionarLibro(${libro.id}, '${libro.titulo}', '${libro.autor}')` : ''}">
                        <div class="result-info">
                            <strong>${libro.titulo}</strong>
                            <small>${libro.autor}</small><br>
                            <small>ISBN: ${libro.isbn}</small>
                        </div>
                        <span class="result-badge ${libro.disponibles > 0 ? 'disponible' : 'no-disponible'}">
                            ${libro.disponibles} disponibles
                        </span>
                    </div>
                `).join('');
            }

            function seleccionarLibro(id, titulo, autor) {
                document.getElementById('libroSeleccionado').value = id;
                document.getElementById('buscarLibro').value = `${titulo} - ${autor}`;
                document.getElementById('resultadosLibro').innerHTML = '';
            }

            // Buscar Préstamo Activo
            let timeoutPrestamo;
            function buscarPrestamoActivo(texto) {
                clearTimeout(timeoutPrestamo);
                
                if (texto.length < 2) {
                    document.getElementById('resultadosPrestamo').innerHTML = '';
                    return;
                }
                
                timeoutPrestamo = setTimeout(async () => {
                    try {
                        const response = await fetch(`api/buscar_prestamo_activo.php?q=${encodeURIComponent(texto)}`);
                        const prestamos = await response.json();
                        
                        mostrarResultadosPrestamo(prestamos);
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }, 300);
            }

            function mostrarResultadosPrestamo(prestamos) {
                const container = document.getElementById('resultadosPrestamo');
                
                if (prestamos.length === 0) {
                    container.innerHTML = '<p class="text-muted" style="padding: 10px;">No se encontraron préstamos activos</p>';
                    return;
                }
                
                container.innerHTML = prestamos.map(prestamo => `
                    <div class="search-result-item" onclick="seleccionarPrestamo(${prestamo.id}, '${prestamo.titulo}', '${prestamo.cliente}')">
                        <div class="result-info">
                            <strong>ID: ${prestamo.id} - ${prestamo.titulo}</strong>
                            <small>Cliente: ${prestamo.cliente} (${prestamo.dni})</small><br>
                            <small>Devolución estimada: ${prestamo.fecha_devolucion_estimada}</small>
                        </div>
                        <span class="badge ${prestamo.dias_restantes < 0 ? 'badge-danger' : 'badge-info'}">
                            ${prestamo.dias_restantes < 0 ? `${Math.abs(prestamo.dias_restantes)} días atrasado` : `${prestamo.dias_restantes} días`}
                        </span>
                    </div>
                `).join('');
            }

            function seleccionarPrestamo(id, titulo, cliente) {
                document.getElementById('prestamoSeleccionado').value = id;
                document.getElementById('buscarPrestamo').value = `ID: ${id} - ${titulo} (${cliente})`;
                document.getElementById('resultadosPrestamo').innerHTML = '';
            }

            async function registrarPrestamo() {
                const formData = new FormData(document.getElementById('formNuevoPrestamo'));
                const alerta = document.getElementById('alertaPrestamo');
                
                try {
                    const response = await fetch('api/registrar_prestamo.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        mostrarAlerta(alerta, result.message, 'success');
                        setTimeout(() => {
                            cerrarModal('modalNuevoPrestamo');
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarAlerta(alerta, result.message, 'danger');
                    }
                } catch (error) {
                    mostrarAlerta(alerta, 'Error al registrar el préstamo', 'danger');
                }
            }

            async function registrarDevolucion() {
                const formData = new FormData(document.getElementById('formDevolucion'));
                const alerta = document.getElementById('alertaDevolucion');
                
                try {
                    const response = await fetch('api/registrar_devolucion.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        mostrarAlerta(alerta, result.message, 'success');
                        setTimeout(() => {
                            cerrarModal('modalDevolucion');
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarAlerta(alerta, result.message, 'danger');
                    }
                } catch (error) {
                    mostrarAlerta(alerta, 'Error al registrar la devolución', 'danger');
                }
            }

            function devolverLibro(idPrestamo, titulo, cliente) {
                if (confirm(`¿Confirmar devolución?\n\nLibro: ${titulo}\nCliente: ${cliente}`)) {
                    fetch('api/registrar_devolucion.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id_prestamo=${idPrestamo}`
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert('✓ Devolución registrada correctamente');
                            location.reload();
                        } else {
                            alert('✗ Error: ' + result.message);
                        }
                    })
                    .catch(error => {
                        alert('Error al procesar la devolución');
                    });
                }
            }

            function mostrarAlerta(elemento, mensaje, tipo) {
                elemento.className = `alert alert-${tipo} show`;
                elemento.textContent = mensaje;
                elemento.style.display = 'block';
                
                setTimeout(() => {
                    elemento.classList.remove('show');
                }, 5000);
            }
            </script>

            <!-- Préstamos recientes (visibles para todos) -->
            <div class="section">
                <h2>Préstamos Recientes</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Libro</th>
                            <th>Cliente</th>
                            <th>Fecha Préstamo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.id, l.titulo, c.nombre as cliente, p.fecha_prestamo, 
                                CASE 
                                    WHEN p.fecha_devolucion_real IS NOT NULL THEN 'devuelto'
                                    WHEN CURDATE() > p.fecha_devolucion_estimada THEN 'atrasado'
                                    ELSE 'activo'
                                END as estado
                                FROM prestamos p
                                JOIN libros l ON p.id_libro = l.id
                                JOIN clientes c ON p.id_cliente = c.id
                                ORDER BY p.fecha_prestamo DESC LIMIT 5";
                        $result = $conn->query($sql);
                        
                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['titulo']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_prestamo'])); ?></td>
                            <td>
                                <?php if ($row['estado'] == 'activo'): ?>
                                    <span class="status active">Activo</span>
                                <?php elseif ($row['estado'] == 'atrasado'): ?>
                                    <span class="status atrasado">Atrasado</span>
                                <?php else: ?>
                                    <span class="status devuelto">Devuelto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay préstamos registrados</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function updateDateTime() {
            const now = new Date();
            const options = { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const dateTimeStr = now.toLocaleDateString('es-ES', options).replace(',', '');
            document.getElementById('currentDateTime').textContent = dateTimeStr;
        }
        
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        <?php if (esAdministrador()): ?>
        setTimeout(() => {
            location.reload();
        }, 120000);
        <?php endif; ?>
    </script>
    
    <?php $conn->close(); ?>
</body>
</html>