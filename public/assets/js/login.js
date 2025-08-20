document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    if (loginForm) {
        // Obtenemos una referencia al botón de envío
        const submitButton = loginForm.querySelector('button[type="submit"]');

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // --- INICIO DE LA MEJORA ---
            
            // 1. Guardar el texto original y activar el estado de carga
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Iniciando sesión...
            `;
            
            // --- FIN DE LA MEJORA ---

            const formData = new FormData(this);
            errorMessage.classList.add('d-none');

            fetch('../app/ajax/login_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorMessage.textContent = data.message;
                    errorMessage.classList.remove('d-none');
                    // Restaurar el botón si hay un error
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Error de conexión con el servidor. Intente de nuevo.';
                errorMessage.classList.remove('d-none');
                console.error('Login Error:', error);
                // Restaurar el botón si hay un error de conexión
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
});