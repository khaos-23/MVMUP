document.addEventListener("DOMContentLoaded", function() {
  fetch('/check_session.php') 
    .then(response => response.json()) 
    .then(data => {
      if (data.loggedIn) {
        // Si iniciado sesion actualiza navbar
        const authLink = document.getElementById('auth-link');
        authLink.innerHTML = '<a class="nav-link" href="/configuracion/index.html">Configuración</a>';

        // Mostrar nombre de usuario en footer
        document.getElementById('username').textContent = data.username;
      } else {
        // Si no iniciado sesion redirigir login
        window.location.href = '/inicio_sesion/index.html';
      }
    })
    .catch(error => {
      console.error('Error al verificar la sesión:', error);
    });
});