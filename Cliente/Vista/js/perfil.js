const cloud = document.getElementById("cloud");
const barraLateral = document.querySelector(".barra-lateral");
const spans = document.querySelectorAll("span");
const menu = document.querySelector(".menu");
const main = document.querySelector("main");

menu.addEventListener("click",()=>{
  barraLateral.classList.toggle("max-barra-lateral");
  if(barraLateral.classList.contains("max-barra-lateral")){
      menu.children[0].style.display = "none";
      menu.children[1].style.display = "block";
  }
  else{
      menu.children[0].style.display = "block";
      menu.children[1].style.display = "none";
  }
  if(window.innerWidth<=320){
      barraLateral.classList.add("mini-barra-lateral");
      main.classList.add("min-main");
      spans.forEach((span)=>{
          span.classList.add("oculto");
      });
  }
});

document.addEventListener('DOMContentLoaded', function() {
    // Obtener datos del perfil al cargar la página
    obtenerPerfil();

    function obtenerPerfil() {
        axios.get('../Controlador/perfil.php')
            .then(function(response) {
                if (response.data.error) {
                    console.error(response.data.error);
                } else {
                    mostrarPerfil(response.data);
                }
            })
            .catch(function(error) {
                console.error(error);
            });
    }

    function mostrarPerfil(data) {
        // Actualizar los elementos HTML con los datos del perfil
        document.getElementById('profile-name').textContent = data.nombre_completo;
        document.getElementById('profile-username').textContent = data.usuario_correo; // Mostrar solo el nombre de usuario del correo
        document.getElementById('nombre-completo').textContent = data.nombre_completo;
        document.getElementById('correo-electronico').textContent = data.correo_completo; // Mostrar el correo completo si es necesario
        document.getElementById('carrera').textContent = data.carrera;

        // Mostrar datos en el formulario de edición
        document.getElementById('nombreEdit').value = data.nombre;
        document.getElementById('apellido_paternoEdit').value = data.apellido_paterno;
        document.getElementById('apellido_maternoEdit').value = data.apellido_materno;
        document.getElementById('carreraEdit').value = data.carrera;
        document.getElementById('fotoPerfilActual').src = data.img_perfil ? data.img_perfil : '';
    }

    // Mostrar formulario de edición
    document.getElementById('editarPerfilBtn').addEventListener('click', function() {
        document.querySelector('.profile-card').style.display = 'none';
        document.getElementById('editarPerfil').style.display = 'block';
    });

    // Cancelar edición
    document.getElementById('cancelarEdicion').addEventListener('click', function() {
        document.querySelector('.profile-card').style.display = 'block';
        document.getElementById('editarPerfil').style.display = 'none';
    });

    // Guardar cambios de perfil
    document.getElementById('formEditarPerfil').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(this);
        axios.post('../Controlador/perfil.php', formData)
            .then(function(response) {
                if (response.data.error) {
                    console.error(response.data.error);
                } else {
                    obtenerPerfil(); // Actualizar perfil después de guardar cambios
                    document.querySelector('.profile-card').style.display = 'block';
                    document.getElementById('editarPerfil').style.display = 'none';
                }
            })
            .catch(function(error) {
                console.error(error);
            });
    });
});
