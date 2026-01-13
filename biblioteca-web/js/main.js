document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const mensajeError = document.getElementById('mensajeError');

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const usuario = document.getElementById('usuario').value.trim();
            const password = document.getElementById('password').value;

            if (!usuario || !password) {
                mostrarError('Por favor, complete todos los campos');
                return;
            }

            const btnLogin = loginForm.querySelector('.btn-login');
            const textoOriginal = btnLogin.textContent;
            btnLogin.disabled = true;
            btnLogin.textContent = 'Verificando...';

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        usuario: usuario,
                        password: password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    //Login bien accedido
                    mensajeError.style.display = 'none';
                    btnLogin.textContent = '✓ Accediendo...';
                    btnLogin.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
                    
                    //redirige dash
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 500);
                } else {
                    // Login fallido
                    mostrarError(data.message || 'Usuario o contraseña incorrectos');
                    btnLogin.disabled = false;
                    btnLogin.textContent = textoOriginal;
                    
                    // Limpiar contraseña
                    document.getElementById('password').value = '';
                    document.getElementById('password').focus();
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión. Verifique que el servidor esté activo.');
                btnLogin.disabled = false;
                btnLogin.textContent = textoOriginal;
            }
        });
    }

    function mostrarError(mensaje) {
        mensajeError.textContent = mensaje;
        mensajeError.classList.add('show');
        mensajeError.style.display = 'block';

        setTimeout(() => {
            mensajeError.classList.remove('show');
            setTimeout(() => {
                mensajeError.style.display = 'none';
            }, 300);
        }, 5000);
    }

    const campoUsuario = document.getElementById('usuario');
    if (campoUsuario) {
        campoUsuario.focus();
    }
});

function buscarLibroPrestamo(query) {
    const resultadosDiv = document.getElementById('resultadosLibro');
    
    if (query.length < 2) {
        resultadosDiv.innerHTML = '';
        resultadosDiv.style.display = 'none';
        return;
    }

    fetch(`../ajax/buscar_libro.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(libros => {
            if (libros.length === 0) {
                resultadosDiv.innerHTML = '<div class="no-results">No se encontraron libros</div>';
                resultadosDiv.style.display = 'block';
                return;
            }

            let html = '<div class="results-list">';
            libros.forEach(libro => {
                const disponible = libro.disponibles > 0;
                const estadoClass = disponible ? 'disponible' : 'no-disponible';
                const estadoTexto = disponible ? `✓ ${libro.disponibles} disponible(s)` : '✗ No disponible';
                
                html += `
                    <div class="result-item ${!disponible ? 'disabled' : ''}" 
                         onclick="${disponible ? `seleccionarLibro(${libro.id}, '${libro.titulo.replace(/'/g, "\\'")}', '${libro.autor.replace(/'/g, "\\'")}', ${libro.disponibles})` : ''}">
                        <div class="result-main">
                            <strong>${libro.titulo}</strong>
                            <span class="result-secondary">por ${libro.autor}</span>
                        </div>
                        <div class="result-details">
                            <span class="badge">ISBN: ${libro.isbn}</span>
                            ${libro.categoria ? `<span class="badge">${libro.categoria}</span>` : ''}
                            <span class="badge ${estadoClass}">${estadoTexto}</span>
                        </div>
                        ${libro.ubicacion ? `<div class="result-location">${libro.ubicacion}</div>` : ''}
                    </div>
                `;
            });
            html += '</div>';
            
            resultadosDiv.innerHTML = html;
            resultadosDiv.style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            resultadosDiv.innerHTML = '<div class="error-message">Error al buscar libros</div>';
        });
}

function seleccionarLibro(id, titulo, autor, disponibles) {
    document.getElementById('libroSeleccionado').value = id;
    document.getElementById('buscarLibro').value = `${titulo} - ${autor}`;
    document.getElementById('resultadosLibro').style.display = 'none';
    
    mostrarAlerta(`Libro seleccionado: "${titulo}" (${disponibles} ejemplar(es) disponible(s))`, 'success');
}

function registrarPrestamo() {
    const idCliente = document.getElementById('clienteSeleccionado').value;
    const idLibro = document.getElementById('libroSeleccionado').value;
    const fechaDevolucion = document.getElementById('fechaDevolucion').value;
    const observaciones = document.getElementById('observaciones').value;
    
    // Validar que se haya seleccionado cliente y libro
    if (!idCliente) {
        mostrarAlerta('Por favor, selecciona un cliente', 'error');
        return;
    }
    
    if (!idLibro) {
        mostrarAlerta('Por favor, selecciona un libro', 'error');
        return;
    }
    
    if (!fechaDevolucion) {
        mostrarAlerta('Por favor, indica la fecha de devolución', 'error');
        return;
    }
    
    // Crear FormData
    const formData = new FormData();
    formData.append('id_cliente', idCliente);
    formData.append('id_libro', idLibro);
    formData.append('fecha_devolucion_estimada', fechaDevolucion);
    formData.append('observaciones', observaciones);
    
    // Enviar petición
    fetch('../ajax/registrar_prestamo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            
            document.getElementById('formNuevoPrestamo').reset();
            document.getElementById('clienteSeleccionado').value = '';
            document.getElementById('libroSeleccionado').value = '';
            document.getElementById('resultadosCliente').style.display = 'none';
            document.getElementById('resultadosLibro').style.display = 'none';
            
            setTimeout(() => {
                cerrarModal('modalNuevoPrestamo');
                // Recargar la tabla de préstamos si existe
                if (typeof cargarPrestamos === 'function') {
                    cargarPrestamos();
                }
            }, 2000);
        } else {
            mostrarAlerta(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al registrar el préstamo', 'error');
    });
}

function mostrarAlerta(mensaje, tipo) {
    const alertaDiv = document.getElementById('alertaPrestamo');
    alertaDiv.className = `alert alert-${tipo}`;
    alertaDiv.textContent = mensaje;
    alertaDiv.style.display = 'block';
    
    setTimeout(() => {
        alertaDiv.style.display = 'none';
    }, 5000);
}

function mostrarMensaje(tipo, texto) {
    const div = document.createElement('div');
    div.className = `alert alert-${tipo}`;
    div.textContent = texto;
    div.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px;
        border-radius: 5px;
        z-index: 9999;
        color: ${tipo === 'success' ? '#155724' : '#721c24'};
        background-color: ${tipo === 'success' ? '#d4edda' : '#f8d7da'};
        border: 1px solid ${tipo === 'success' ? '#c3e6cb' : '#f5c6cb'};
    `;
    
    document.body.appendChild(div);
    
    setTimeout(() => {
        div.remove();
    }, 5000);
}

// ===== FUNCIONES PARA ELIMINAR SANCIÓN =====

// Función para eliminar sanción (solo admin)
function eliminarSancion(idCliente, nombreCliente) {
    if (!confirm(`¿Estás seguro de que deseas eliminar la sanción de ${nombreCliente}?`)) {
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
            mostrarAlerta(data.message, 'success');
            // Recargar la tabla de clientes
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            mostrarAlerta(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al eliminar la sanción', 'error');
    });
}

// Función para abrir modal de sanción
function abrirModalSancion(idCliente, nombreCliente) {
    const dias = prompt(`¿Cuántos días deseas sancionar a ${nombreCliente}?`, '15');
    
    if (dias === null) return; // Usuario canceló
    
    if (!dias || isNaN(dias) || dias < 1 || dias > 365) {
        alert('Por favor ingresa un número válido de días entre 1 y 365');
        return;
    }
    
    const motivo = prompt('Motivo de la sanción (opcional):', 'Préstamo no devuelto a tiempo');
    
    const formData = new FormData();
    formData.append('id_cliente', idCliente);
    formData.append('dias_sancion', dias);
    formData.append('motivo', motivo || 'Sanción aplicada por administrador');
    
    fetch('api/aplicar_sancion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            // Recargar la tabla de clientes
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            mostrarAlerta(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al aplicar la sanción', 'error');
    });
}


