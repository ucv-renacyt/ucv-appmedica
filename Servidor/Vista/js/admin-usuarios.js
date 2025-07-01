// Middleware de sesión y carga inicial
let paginaActual = 1;
let terminoBusqueda = "";

// Middleware y eventos al cargar
document.addEventListener('DOMContentLoaded', function () {
  fetch('../Controlador/cusuarios.php')
    .then(response => response.text())
    .then(text => {
      if (text.includes("Usuario no autenticado")) {
        throw new Error('Usuario no autenticado');
      }
    })
    .catch(error => {
      console.error('Error al cargar usuarios:', error);
      window.location.href = '../Controlador/cerrarSesion.php';
    });

  cargarUsuarios();

  // Búsqueda
  let debounceTimer;
  document.querySelector(".search-input").addEventListener("input", function () {
    clearTimeout(debounceTimer);
    terminoBusqueda = this.value.trim();
    debounceTimer = setTimeout(() => {
      paginaActual = 1;
      cargarUsuariosConParametros();
    }, 300);
  });

  // Paginación
// Paginación
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("pagination-button")) {
    e.preventDefault();
    const texto = e.target.textContent.trim();

    // Quitar la clase 'active' de todos los botones
    document.querySelectorAll(".pagination-button").forEach(btn => btn.classList.remove("active"));

    // Avanzar
    if (e.target.classList.contains("siguiente") || e.target.querySelector("i.bx-chevron-right")) {
      paginaActual++;
    }
    // Retroceder
    else if (e.target.classList.contains("anterior") || e.target.querySelector("i.bx-chevron-left")) {
      if (paginaActual > 1) {
        paginaActual--;
      }
    }
    // Ir a página específica
    else if (!isNaN(texto)) {
      paginaActual = parseInt(texto);
    }

    cargarUsuariosConParametros();

    setTimeout(() => {
      const botones = document.querySelectorAll(".pagination-button");
      if (botones[paginaActual - 1]) {
        botones.forEach(btn => btn.classList.remove("active"));
        botones[paginaActual - 1].classList.add("active");
      }
    }, 100);
  }
});
});

// Cargar usuarios por defecto
function cargarUsuarios() {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "../Controlador/cusuarios.php", true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById("tabla-usuarios").innerHTML = xhr.responseText;
      asignarEventosBotones();
    }
  };
  xhr.send();
}

function asignarEventosBotones() {
  document.querySelectorAll(".ver-usuario").forEach(btn =>
    btn.addEventListener("click", () => verUsuario(btn.getAttribute("data-id")))
  );
  document.querySelectorAll(".editar-usuario").forEach(btn =>
    btn.addEventListener("click", () => editarUsuario(btn.getAttribute("data-id")))
  );
}

function cargarUsuariosConParametros() {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", `../Controlador/cusuarios.php?pagina=${paginaActual}&buscar=${encodeURIComponent(terminoBusqueda)}`, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      // Eliminar paginación anterior
      const paginacionAntigua = document.querySelector("#tabla-usuarios .pagination");
      if (paginacionAntigua) {
        paginacionAntigua.remove();
      }

      // Insertar nueva tabla (con nueva paginación incluida desde PHP)
      document.getElementById("tabla-usuarios").innerHTML = xhr.responseText;

      asignarEventosBotones();
    }
  };
  xhr.send();
}

function verUsuario(idUsuario) {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "../Controlador/cusuarios.php?action=ver&id_usuario=" + idUsuario, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById("modalVerUsuarioLabel").innerText = "Detalles del Usuario";
      document.getElementById("modalVerUsuarioBody").innerHTML = xhr.responseText;
      new bootstrap.Modal(document.getElementById('modalVerUsuario')).show();
    }
  };
  xhr.send();
}

function editarUsuario(idUsuario) {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "../Controlador/cusuarios.php?action=editar&id_usuario=" + idUsuario, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById("modalEditarUsuarioLabel").innerText = "Editar Usuario";
      document.getElementById("modalEditarUsuarioBody").innerHTML = xhr.responseText;
      const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
      modal.show();

      document.getElementById("formEditarUsuario").addEventListener("submit", function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        guardarCambiosUsuario(formData);
      });
    }
  };
  xhr.send();
}

function guardarCambiosUsuario(formData) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "../Controlador/cusuarios.php", true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      cargarUsuariosConParametros();
      bootstrap.Modal.getInstance(document.getElementById('modalEditarUsuario')).hide();
    }
  };
  xhr.send(formData);
}
