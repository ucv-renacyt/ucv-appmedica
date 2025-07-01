document.addEventListener("DOMContentLoaded", function() {
    function cargarHistorial() {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "../Controlador/historial.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById("tabla-historial").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    // Cargar el historial cuando se cargue la página
    cargarHistorial();

    // Manejar el clic en los botones "Ver" para cargar los detalles de la alerta en una ventana modal
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('ver-alerta')) {
            var idAlerta = event.target.getAttribute('data-id-alerta');
            cargarDetalleAlerta(idAlerta);
        }
    });

    function cargarDetalleAlerta(idAlerta) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "../Controlador/historial.php?id_alerta=" + idAlerta, true);
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

                // Mostrar los detalles en el modal
                document.getElementById('modalBody').innerHTML = `
                    <p><strong>ID de la Alerta:</strong> ${detallesAlerta.id_alerta}</p>
                    <p><strong>Ubicación:</strong> ${detallesAlerta.ubicacion}</p>
                    ${detallesAlerta.piso ? `<p><strong>Piso:</strong> ${detallesAlerta.piso}</p>` : ''}
                    <p><strong>Especificación:</strong> ${detallesAlerta.especificacion}</p>
                    <p><strong>Descripción:</strong> ${detallesAlerta.descripcion}</p>
                    <p><strong>Síntomas:</strong> ${detallesAlerta.sintomas}</p>
                    ${detallesAlerta.notas ? `<p><strong>Notas:</strong> ${detallesAlerta.notas}</p>` : ''}
                    <p><strong>Fecha:</strong> ${fechaFormateada} | ${horaFormateada}</p>
                    <p><strong>Estado:</strong> <button class="btn ${estadoColor}" disabled>${detallesAlerta.estado}</button></p>
                `;

                // Mostrar el modal
                document.getElementById('alertaModal').classList.add('show');
                document.body.classList.add('modal-open');
            }
        };
        xhr.send();
    }

    // Manejar el cierre del modal para limpiar el contenido
    document.getElementById('alertaModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('modalBody').innerHTML = '';
    });

    // Función para obtener el color de estado basado en el valor del estado
    function getEstadoColor(estado) {
        switch (estado) {
            case 'Pendiente':
                return 'btn-danger btn-sm';
            case 'En proceso':
                return 'btn-warning btn-sm';
            case 'Atendido':
                return 'btn-success btn-sm';
            default:
                return 'btn-secondary btn-sm';
        }
    }
});
