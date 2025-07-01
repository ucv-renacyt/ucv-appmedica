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
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        loadingScreen.style.display = 'flex'; // Mostrar pantalla de carga
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 2000); // Cambiar según sea necesario el tiempo de carga
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

            // Verificar si se está en admin-main.html y ocultar la pantalla de carga si se vuelve atrás
            window.addEventListener('pageshow', function(event) {
                const historyTraversal = event.persisted || 
                                       (typeof window.performance != 'undefined' && 
                                        window.performance.navigation.type === 2);
                if (historyTraversal) {
                    loadingScreen.style.display = 'none';
                }
            });
        });
        