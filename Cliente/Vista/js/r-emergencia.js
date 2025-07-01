document.addEventListener("DOMContentLoaded", function() {
    // Cargar las emergencias cuando se cargue la página
    cargarEmergencias();

    // Manejar el clic en los botones "Ver", "Cancelar" y "Editar"
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('ver-alerta')) {
            var idAlerta = event.target.getAttribute('data-id-alerta');
            cargarDetalleAlerta(idAlerta);
        } else if (event.target.classList.contains('cancelar-alerta')) {
            var idAlerta = event.target.getAttribute('data-id-alerta');
            mostrarModalConfirmacion(idAlerta);
        } else if (event.target.classList.contains('editar-alerta')) {
            var idAlerta = event.target.getAttribute('data-id-alerta');

            var modal = document.getElementById('modalBody');
            var ubicacion = modal.querySelector('p:nth-child(2)').innerText.replace("Ubicación:", "").trim();
            var piso = modal.querySelector('p:nth-child(3)')?.innerText.includes('Piso') ? modal.querySelector('p:nth-child(3)').innerText.replace("Piso:", "").trim() : '';
            var especificacion = modal.querySelector('p:nth-child(4)').innerText.replace("Especificación:", "").trim();
            var descripcion = modal.querySelector('p:nth-child(5)').innerText.replace("Descripción:", "").trim();
            var sintomas = modal.querySelector('p:nth-child(6)').innerText.replace("Síntomas:", "").trim();
            var notas = modal.querySelector('p:nth-child(7)')?.innerText.includes('Notas') ? modal.querySelector('p:nth-child(7)').innerText.replace("Notas:", "").trim() : '';

            modal.innerHTML = `
                <form id="formEditarAlerta">
                    <input type="hidden" name="id_alerta" value="${idAlerta}">
                    <input type="hidden" name="editar_alerta" value="1">
                    <div class="mb-2"><label>Ubicación:</label><input class="form-control" name="ubicacion" value="${ubicacion}" required></div>
                    <div class="mb-2"><label>Piso:</label><input class="form-control" name="piso" value="${piso}"></div>
                    <div class="mb-2"><label>Especificación:</label><input class="form-control" name="especificacion" value="${especificacion}" required></div>
                    <div class="mb-2"><label>Descripción:</label><textarea class="form-control" name="descripcion" required>${descripcion}</textarea></div>
                    <div class="mb-2"><label>Síntomas:</label><textarea class="form-control" name="sintomas" required>${sintomas}</textarea></div>
                    <div class="mb-2"><label>Notas:</label><textarea class="form-control" name="notas">${notas}</textarea></div>
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            `;

            document.getElementById("formEditarAlerta").addEventListener("submit", function (e) {
                e.preventDefault();
                const datos = new FormData(this);

                fetch("../Controlador/r-emergencia.php", {
                    method: "POST",
                    body: datos
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        cargarEmergencias();
                        bootstrap.Modal.getInstance(document.getElementById("alertaModal")).hide();
                    }
                })
                .catch(() => alert("Error al guardar los cambios."));
            });
        }
    });

    function cargarEmergencias() {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "../Controlador/r-emergencia.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById("tabla-emergencias").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    function cargarDetalleAlerta(idAlerta) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "../Controlador/r-emergencia.php?id_alerta=" + idAlerta, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var detallesAlerta = JSON.parse(xhr.responseText);
                var fecha = new Date(detallesAlerta.fecha);
                var opcionesFecha = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                var fechaFormateada = fecha.toLocaleDateString('es-PE', opcionesFecha);
                var horaFormateada = fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                fechaFormateada = fechaFormateada.replace(/^\w/, match => match.toUpperCase());
                var estadoColor = getEstadoColor(detallesAlerta.estado);

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
                    ${detallesAlerta.estado === 'Pendiente' || detallesAlerta.estado === 'En proceso' ? `
                        <button class="btn btn-danger cancelar-alerta" data-id-alerta="${detallesAlerta.id_alerta}">Cancelar</button>
                        <button class="btn btn-primary editar-alerta" data-id-alerta="${detallesAlerta.id_alerta}">Editar</button>
                    ` : ''}
                `;

                document.getElementById('alertaModal').classList.add('show');
                document.body.classList.add('modal-open');
            }
        };
        xhr.send();
    }

    function mostrarModalConfirmacion(idAlerta) {
        var confirmacionModal = new bootstrap.Modal(document.getElementById('confirmacionModal'));
        confirmacionModal.show();

        document.getElementById('confirmarCancelacion').onclick = function() {
            cancelarAlerta(idAlerta);
            confirmacionModal.hide();
        };
    }

    function cancelarAlerta(idAlerta) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../Controlador/r-emergencia.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var respuesta = JSON.parse(xhr.responseText);
                document.getElementById('mensajeModalTexto').textContent = respuesta.message;
                var mensajeModal = new bootstrap.Modal(document.getElementById('mensajeModal'));
                mensajeModal.show();
                if (respuesta.success) {
                    cargarEmergencias();
                }
            }
        };
        xhr.send("id_alerta=" + idAlerta + "&action=cancelar");
    }

    document.getElementById('mensajeModal').addEventListener('hidden.bs.modal', function () {
        cargarEmergencias();
    });

    function getEstadoColor(estado) {
        switch (estado) {
            case 'Pendiente': return 'btn-danger btn-sm';
            case 'En proceso': return 'btn-warning btn-sm';
            case 'Atendido': return 'btn-success btn-sm';
            case 'Cancelado': return 'btn-secondary btn-sm';
            default: return 'btn-secondary btn-sm';
        }
    }
});
