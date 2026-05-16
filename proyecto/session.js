// Control de sesión y personalización de botones en la vista principal

document.addEventListener('DOMContentLoaded', function() {
  // Simulación de sesión (cambia a false para probar sin sesión)
  const sesionActiva = false; // Cambia a true para simular sesión activa

  // Espera a que el header esté cargado
  function renderNavSession() {
    const navSession = document.getElementById('nav-session-actions');
    if (!navSession) return;
    if (!sesionActiva) {
      navSession.innerHTML = `
        <button id="login-btn" class="nav-btn"><i class="fa-regular fa-user"></i> Iniciar sesión</button>
        <button id="register-btn" class="nav-btn"><i class="fa-solid fa-user-plus"></i> Registrarse</button>
        <span style="margin-left:12px;font-size:1.7em;color:#bdbdbd;vertical-align:middle;">
          <i class="fa-regular fa-circle-user"></i>
        </span>
      `;
    } else {
      navSession.innerHTML = `
        <a href="publish.html" class="nav-btn btn-primary" style="margin-left:8px"><i class="fa-solid fa-plus"></i> Publicar</a>
        <a href="profile.html">
          <img class="nav-avatar" src="chef-profile.png" alt="Avatar" />
        </a>
      `;
    }
  }

  // Si el header se carga dinámicamente, espera a que esté listo
  if (document.getElementById('nav-session-actions')) {
    renderNavSession();
  } else {
    const observer = new MutationObserver(() => {
      if (document.getElementById('nav-session-actions')) {
        renderNavSession();
        observer.disconnect();
      }
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }
});
