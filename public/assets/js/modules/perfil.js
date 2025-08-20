$(document).ready(function() {
    const form = $('#passwordChangeForm');
    if (!form.length) return; // Salir si no estamos en la página correcta

    const errorDiv = $('#password-error-message');
    const successDiv = $('#password-success-message');

    form.on('submit', function(e) {
        e.preventDefault();
        
        // Ocultar mensajes previos
        errorDiv.addClass('d-none');
        successDiv.addClass('d-none');

        const nueva = $('#password_nueva').val();
        const confirmar = $('#password_confirmar').val();

        // Validación en el cliente antes de enviar
        if (nueva !== confirmar) {
            errorDiv.text('Las nuevas contraseñas no coinciden.').removeClass('d-none');
            return;
        }

        const formData = $(this).serialize() + '&action=cambiar_password';

        $.post('../app/ajax/perfil_ajax.php', formData, function(response) {
            if (response.success) {
                form[0].reset(); // Limpiar el formulario
                successDiv.text(response.message).removeClass('d-none');
                
                // Opcional: Mostrar un SweetAlert y redirigir
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message + ' Serás redirigido en 3 segundos.',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = 'index.php';
                });

            } else {
                errorDiv.text(response.message).removeClass('d-none');
            }
        }, 'json');
    });
});