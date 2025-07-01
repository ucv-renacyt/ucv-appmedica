document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('adminLoginForm');
    const loadingScreen = document.getElementById('loadingScreen');
    const loginError = document.getElementById('loginError');

    loginForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);

        try {
            const response = await fetch('../Controlador/clogin.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });

            const result = await response.json();

            if (result.success) {
                // Guarda el token en localStorage
                if (result.token) {
                    localStorage.setItem('token', result.token);
                }

                // Muestra la pantalla de carga
                loadingScreen.style.display = 'flex';
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 2000); // Puedes ajustar el tiempo
            } else {
                loginError.style.display = 'block';
                loginError.textContent = result.message || 'Credenciales inválidas.';
            }
        } catch (error) {
            console.error('Error:', error);
            loginError.style.display = 'block';
            loginError.textContent = 'Error al iniciar sesión. Inténtelo de nuevo más tarde.';
        }
    });

    // Oculta la pantalla de carga si se vuelve atrás con el navegador
    window.addEventListener('pageshow', function(event) {
        const historyTraversal = event.persisted || 
                                (typeof window.performance !== 'undefined' && 
                                 window.performance.navigation.type === 2);
        if (historyTraversal) {
            loadingScreen.style.display = 'none';
        }
    });
});
