document.addEventListener("DOMContentLoaded", function() {
  // Cambiar contraseña
  const changePasswordForm = document.getElementById('change-password-form');
  const passwordMessage = document.getElementById('password-message');
  if (changePasswordForm) {
    changePasswordForm.addEventListener('submit', function(event) {
      event.preventDefault(); 
      const formData = new FormData(changePasswordForm);
      fetch('/configuracion/cambiar_contrasena.php', {
      method: 'POST',
      body: formData
      })
      .then(response => response.text()) // Respuesta servidor
      .then(data => {
        // Mostrar mensaje respuesta
        passwordMessage.innerHTML = `<div class="alert alert-success">${data}</div>`;
      })
      .catch(error => {
        // Mensaje error
        passwordMessage.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
      });
    });
  }
  
  // Cambiar correo
  const changeEmailForm = document.getElementById('change-email-form');
  const emailMessage = document.getElementById('email-message');
  if (changeEmailForm) {
    changeEmailForm.addEventListener('submit', function(event) {
    event.preventDefault(); 
    const formData = new FormData(changeEmailForm);
    fetch('/configuracion/cambiar_correo.php', {
      method: 'POST',
      body: formData
      })
      .then(response => response.text()) 
      .then(data => {
        emailMessage.innerHTML = `<div class="alert alert-success">${data}</div>`;
      })
      .catch(error => {
        emailMessage.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
      });
    });
  }
  
  // Cambiar nombre de usuario
  const changeUsernameForm = document.getElementById('change-username-form');
  const usernameMessage = document.getElementById('username-message');
  if (changeUsernameForm) {
    changeUsernameForm.addEventListener('submit', function(event) {
      event.preventDefault(); 
      const formData = new FormData(changeUsernameForm);
      fetch('/configuracion/cambiar_username.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text()) 
      .then(data => {
        usernameMessage.innerHTML = `<div class="alert alert-success">${data}</div>`;
      })
      .catch(error => {
        usernameMessage.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
      });
    });
  }

  // Cerrar sesion
  const logoutForm = document.getElementById('logout-form');
  const logoutMessage = document.getElementById('logout-message');

  if (logoutForm) {
    logoutForm.addEventListener('submit', function(event) {
      event.preventDefault(); 

      fetch('/configuracion/cerrar_sesion.php', {
        method: 'POST'
      })
      .then(response => response.text()) 
      .then(data => {
        
        logoutMessage.innerHTML = `<div class="alert alert-success">${data}</div>`;
  
        setTimeout(() => {
          window.location.href = '/index.html';
        }, 2000); // Redirigir 2 segundos
      })
      .catch(error => {
        
        logoutMessage.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
      });
    });
  }
});