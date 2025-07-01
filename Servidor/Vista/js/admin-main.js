const cloud = document.getElementById("cloud");
const barraLateral = document.querySelector(".barra-lateral");
const spans = document.querySelectorAll("span");
const menu = document.querySelector(".menu");
const main = document.querySelector("main");

document.addEventListener('DOMContentLoaded', function() {
    fetch('../Controlador/cmain.php')
        .then(response => response.text())
        .then(text => {
            if (text.includes("Usuario no autenticado")) {
                throw new Error('Usuario no autenticado');
            }
            // Si necesitas usar los datos, parsea aquí el JSON si aplica
            // const data = JSON.parse(text);
        })
        .catch(error => {
            console.error('Error al cargar main:', error);
            window.location.href = '../Controlador/cerrarSesion.php';
        });
});


menu.addEventListener("click",()=>{
  barraLateral.classList.toggle("max-barra-lateral");
   if(menu.children.length >= 2 ){
  if(barraLateral.classList.contains("max-barra-lateral")){
      menu.children[0].style.display = "none";
      menu.children[1].style.display = "block";
  }
  else{
      menu.children[0].style.display = "block";
      menu.children[1].style.display = "none";
  }
}
  if(window.innerWidth<=320){
      barraLateral.classList.add("mini-barra-lateral");
      main.classList.add("min-main");
      spans.forEach((span)=>{
          span.classList.add("oculto");
      });
  }
});


document.addEventListener("DOMContentLoaded", function() {
    function cargarAlertas() {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "../Controlador/cmain.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById("tabla-alertas").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    // Cargar las alertas cuando se cargue la página
    cargarAlertas();

    // Manejar el clic en los botones "Ver" para cargar los detalles de la alerta en una ventana modal
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('ver-alerta')) {
            var idAlerta = event.target.getAttribute('data-id-alerta');
            cargarDetalleAlerta(idAlerta);
        }
    });

    // Manejar el clic en los botones "Editar" para abrir el modal de editar estado
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('editar-alerta')) {
            var idAlerta = event.target.getAttribute('data-id-alerta');
            document.getElementById('idAlertaEditar').value = idAlerta;

            // Obtener el estado actual y seleccionarlo en el select
            var estadoActual = event.target.getAttribute('data-estado');
            var selectEstado = document.getElementById('estadoAlerta');
            var option = selectEstado.querySelector(`option[value="${estadoActual}"]`);
            if (option) {
                option.selected = true;
            }

            // Mostrar el modal para editar el estado
            var modal = new bootstrap.Modal(document.getElementById('editarEstadoModal'), {
                keyboard: false
            });
            modal.show();
        }
    });

    // Manejar el clic en el botón "Guardar" para guardar el nuevo estado
    document.getElementById('guardarEstado').addEventListener('click', function() {
        var idAlerta = document.getElementById('idAlertaEditar').value;
        var nuevoEstado = document.getElementById('estadoAlerta').value;

        // Realizar la solicitud para actualizar el estado
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../Controlador/cmain.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Recargar la tabla de alertas después de actualizar
                cargarAlertas();
                // Cerrar el modal de edición de estado
                var modal = bootstrap.Modal.getInstance(document.getElementById('editarEstadoModal'));
                modal.hide();
            }
        };
        xhr.send(`id_alerta=${idAlerta}&nuevo_estado=${nuevoEstado}`);
    });

    function cargarDetalleAlerta(idAlerta) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "../Controlador/cmain.php?id_alerta=" + idAlerta, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Obtener los detalles de la alerta desde la respuesta JSON
                var detallesAlerta = JSON.parse(xhr.responseText);

                // Formatear la fecha
                var fecha = new Date(detallesAlerta.fecha);
                var opcionesFecha = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                var fechaFormateada = fecha.toLocaleDateString('es-PE', opcionesFecha);
                var horaFormateada = fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });

                // Aplicar ucfirst solo al primer carácter del día
                fechaFormateada = fechaFormateada.replace(/^\w/, function(match) {
                    return match.toUpperCase();
                });

                // Obtener el color del estado
                var estadoColor = getEstadoColor(detallesAlerta.estado);

                // Construir el contenido del modal
                var modalBody = `
                    <p><strong>Fecha:</strong> ${fechaFormateada} | ${horaFormateada}</p>
                    <p><strong>Ubicación:</strong> ${detallesAlerta.ubicacion}</p>
                    ${detallesAlerta.piso ? `<p><strong>Piso:</strong> ${detallesAlerta.piso}</p>` : ''}
                    <p><strong>Especificación:</strong> ${detallesAlerta.especificacion}</p>
                    <p><strong>Descripción:</strong> ${detallesAlerta.descripcion}</p>
                    <p><strong>Síntomas:</strong> ${detallesAlerta.sintomas}</p>
                    ${detallesAlerta.notas ? `<p><strong>Notas:</strong> ${detallesAlerta.notas}</p>` : ''}
                    <p><strong>Estado:</strong> <button class="btn ${estadoColor}" disabled>${detallesAlerta.estado}</button></p>
                    <p><strong>Reportado por:</strong> ${detallesAlerta.nombre} ${detallesAlerta.apellido_paterno} ${detallesAlerta.apellido_materno}</p>
                    <p><strong>Correo:</strong> ${detallesAlerta.correo_institucional}</p>
                    <p><strong>Carrera:</strong> ${detallesAlerta.carrera}</p>
                `;
                document.getElementById("modalBody").innerHTML = modalBody;

                // Mostrar el modal
                var modal = new bootstrap.Modal(document.getElementById('alertaModal'), {
                    keyboard: false
                });
                modal.show();
            }
        };
        xhr.send();
    }
    // Función para obtener el color de estado basado en el valor del estado
    function getEstadoColor(estado) {
        switch (estado) {
            case 'Pendiente':
                return 'btn-danger btn-sm';
            case 'En proceso':
                return 'btn-warning btn-sm';
            case 'Atendido':
                return 'btn-success btn-sm';
            case 'Cancelado':
                return 'btn-secondary btn-sm';
            default:
                return 'btn-secondary btn-sm';
        }
    }
});