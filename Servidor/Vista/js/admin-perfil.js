const cloud = document.getElementById("cloud");
const barraLateral = document.querySelector(".barra-lateral");
const spans = document.querySelectorAll("span");
const menu = document.querySelector(".menu");
const main = document.querySelector("main");

// --- Menú lateral ---
menu.addEventListener("click", () => {
    barraLateral.classList.toggle("max-barra-lateral");
    if (menu.children.length >= 2) {
        if (barraLateral.classList.contains("max-barra-lateral")) {
            menu.children[0].style.display = "none";
            menu.children[1].style.display = "block";
        } else {
            menu.children[0].style.display = "block";
            menu.children[1].style.display = "none";
        }
    }
    if (window.innerWidth <= 320) {
        barraLateral.classList.add("mini-barra-lateral");
        main.classList.add("min-main");
        spans.forEach(span => span.classList.add("oculto"));
    }
});

cloud.addEventListener("click", () => {
    barraLateral.classList.toggle("mini-barra-lateral");
    main.classList.toggle("min-main");
    spans.forEach(span => span.classList.toggle("oculto"));
});

// --- Botones de edición ---
document.getElementById('editarPerfilBtn').addEventListener('click', () => {
    document.querySelector('.card').style.display = 'none';
    document.getElementById('editarPerfil').style.display = 'block';
});

document.getElementById('cancelarEdicion').addEventListener('click', () => {
    document.getElementById('editarPerfil').style.display = 'none';
    document.querySelector('.card').style.display = 'block';
});

// --- Previsualizar imagen ---
document.getElementById('fotoPerfil').addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('fotoPerfilActual').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// --- Cargar datos de perfil al iniciar ---
document.addEventListener('DOMContentLoaded', function () {
    const token = localStorage.getItem('token'); // ← Token guardado del login

    fetch('../../Servidor/Controlador/cperfil.php', {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Authorization': token ? `Bearer ${token}` : ''
        }
    })
        .then(response => response.text())
        .then(text => {
            if (!text) throw new Error("Respuesta vacía");
            const data = JSON.parse(text);
            if (data.error === 'Usuario no autenticado') {
                window.location.href = '../admin-login.html';
            } else {
                mostrarDatosPerfil(data);
            }
        })
        .catch(error => {
            console.error('Error al cargar datos del perfil:', error);
            mostrarModalError("No se pudo cargar tu perfil.");
        });
});

// --- Mostrar datos en la vista ---
function mostrarDatosPerfil(data) {
    document.getElementById('nombre').textContent = data.nombre || '';
    document.getElementById('apellidos').textContent = `${data.apellido_paterno || ''} ${data.apellido_materno || ''}`;
    document.getElementById('telefono').textContent = data.telefono || '';
    document.getElementById('correo').textContent = data.correo_institucional || '';
    document.getElementById('carrera').textContent = data.carrera || '';

    document.getElementById('nombreEdit').value = data.nombre || '';
    document.getElementById('apellido_paternoEdit').value = data.apellido_paterno || '';
    document.getElementById('apellido_maternoEdit').value = data.apellido_materno || '';
    document.getElementById('telefonoEdit').value = data.telefono || '';
    document.getElementById('carreraEdit').value = data.carrera || '';

if (data.img_perfil && data.img_perfil.startsWith('data:image')) {
    document.getElementById('fotoPerfilActual').src = data.img_perfil;
} else {
    document.getElementById('fotoPerfilActual').src = 'img/default.png'; // imagen de respaldo si no hay
}

    document.querySelector('.card').style.display = 'block';
    document.getElementById('editarPerfil').style.display = 'none';
}

// --- Guardar cambios del perfil ---
document.getElementById('formEditarPerfil').addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);
    const token = localStorage.getItem('token');

    fetch('../../Servidor/Controlador/cperfil.php', {
        method: 'POST',
        body: formData,
        credentials: 'include',
        headers: {
            'Authorization': token ? `Bearer ${token}` : ''
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarModalExito();
            } else {
                mostrarModalError(data.error);
            }
        })
        .catch(error => {
            mostrarModalError('Error de conexión. Inténtelo nuevamente.');
        });
});

// --- Modales de éxito y error ---
function mostrarModalExito() {
    const myModal = new bootstrap.Modal(document.getElementById('modalExito'), {
        keyboard: false
    });
    myModal.show();
    setTimeout(() => {
        myModal.hide();
        window.location.href = 'admin-perfil.html';
    }, 2000);
}

function mostrarModalError(mensaje) {
    const modalError = document.getElementById('modalError');
    const modalMensaje = document.getElementById('mensajeError');
    modalMensaje.textContent = mensaje;
    const myModal = new bootstrap.Modal(modalError, {
        keyboard: false
    });
    myModal.show();
}
