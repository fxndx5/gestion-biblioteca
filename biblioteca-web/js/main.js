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
                    // Login exitoso
                    mensajeError.style.display = 'none';
                    btnLogin.textContent = '✓ Accediendo...';
                    btnLogin.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
                    
                    // Redirigir al dashboard
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