$(document).ready(function() {
    const tablaBody = $('#tablaUsuariosBody');
    if (!tablaBody.length) return; // Salir si no estamos en la página de usuarios

    const modalElement = document.getElementById('usuarioModal');
    const modal = new bootstrap.Modal(modalElement);
    const modalTitle = $('#usuarioModalTitle');
    const form = $('#usuarioForm');
    const idUsuarioInput = $('#id_usuario');
    const passwordInput = $('#password');
    const passwordHelp = $('#passwordHelp');

    const cargarUsuarios = () => {
        $.getJSON('../app/ajax/usuarios_ajax.php?action=listar', function(response) {
            tablaBody.empty();
            if (response.success) {
                response.data.forEach(user => {
                    const isChecked = user.activo == 1 ? 'checked' : '';
                    const badgeClass = user.activo == 1 ? 'bg-success' : 'bg-danger';
                    const badgeText = user.activo == 1 ? 'Activo' : 'Inactivo';
                    
                    tablaBody.append(`
                        <tr>
                            <td>${user.nombre_usuario}</td>
                            <td>${user.correo}</td>
                            <td><span class="badge bg-secondary">${user.nombre_rol}</span></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input estado-switch" type="checkbox" data-id="${user.id_usuario}" ${isChecked}>
                                    <label class="form-check-label">${badgeText}</label>
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-warning btn-sm btn-editar" data-id="${user.id_usuario}" title="Editar Usuario">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
        });
    };

    $('#btnNuevoUsuario').on('click', function() {
        form[0].reset();
        idUsuarioInput.val('');
        modalTitle.text('Nuevo Usuario');
        passwordInput.prop('required', true);
        passwordHelp.text('La contraseña es obligatoria para nuevos usuarios.');
        modal.show();
    });

    tablaBody.on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        $.getJSON(`../app/ajax/usuarios_ajax.php?action=obtener&id=${id}`, function(response) {
            if (response.success) {
                const user = response.data;
                idUsuarioInput.val(user.id_usuario);
                $('#nombre_usuario').val(user.nombre_usuario);
                $('#correo').val(user.correo);
                $('#id_rol').val(user.id_rol);
                passwordInput.prop('required', false);
                passwordHelp.text('Dejar en blanco para no cambiar la contraseña.');
                modalTitle.text('Editar Usuario');
                modal.show();
            }
        });
    });

    form.on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.post('../app/ajax/usuarios_ajax.php?action=guardar', formData, function(data) {
            if (data.success) {
                modal.hide();
                Swal.fire('¡Éxito!', data.message, 'success');
                cargarUsuarios();
            } else {
                Swal.fire('Error', data.message || 'No se pudo guardar el usuario.', 'error');
            }
        }, 'json');
    });

    tablaBody.on('change', '.estado-switch', function() {
        const id = $(this).data('id');
        const estado = $(this).is(':checked') ? 1 : 0;
        const label = $(this).next('label');
        
        $.post('../app/ajax/usuarios_ajax.php?action=cambiar_estado', { id, estado }, function(response) {
            if(response.success){
                label.text(estado === 1 ? 'Activo' : 'Inactivo');
                label.closest('div').find('.form-check-input').prop('checked', estado === 1);
            }
        }, 'json');
    });

    cargarUsuarios();
});