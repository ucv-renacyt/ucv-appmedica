document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('userLoginForm');
    const loadingScreen = document.getElementById('loadingScreen');
    const loginError = document.getElementById('loginError');

    // ---  Middleware: Verificar sesión activa al cargar la página ---
    verificarSesionExistente();

    // ---  Manejo de envío de formulario ---
    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);

        try {
            const response = await fetch('../Controlador/login.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                loadingScreen.style.display = 'flex';
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 2000);
            } else {
                loginError.style.display = 'block';
                loginError.textContent = result.message;
            }
        } catch (error) {
            console.error('Error:', error);
            loginError.style.display = 'block';
            loginError.textContent = 'Error al iniciar sesión. Inténtelo de nuevo más tarde.';
        }
    });

    // --- Si vuelve atrás desde main.html, quitar loading ---
    window.addEventListener('pageshow', function (event) {
        const historyTraversal = event.persisted ||
            (typeof window.performance != 'undefined' &&
                window.performance.navigation.type === 2);
        if (historyTraversal) {
            loadingScreen.style.display = 'none';
        }
    });
});

// Función middleware de sesión
function verificarSesionExistente() {
    fetch('../Controlador/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'verificar_sesion=1'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        }
    })
    .catch(err => {
        console.error('No se pudo verificar la sesión:', err);
    });
}
