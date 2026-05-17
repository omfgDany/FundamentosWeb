async function checkSession() {
  const sessionArea = document.getElementById('nav-session-actions');
  const publishArea = document.getElementById('nav-publish-container');
  if (!sessionArea) return;

  try {
    const res = await fetch('auth/session_check.php');
    if (!res.ok) throw new Error("Error en respuesta del servidor");
    const data = await res.json();

    if (data && data.loggedIn === true) {
      renderUserMenu(sessionArea, data.user);
      if (publishArea) {
        publishArea.innerHTML = `
          <a href="publish.html" style="text-decoration: none; background: #f97316; color: white; padding: 8px 16px; border-radius: 8px; font-weight: bold; font-size: 0.9rem; display: flex; align-items: center; gap: 6px; transition: background 0.2s;" onmouseover="this.style.background='#e05e0d'" onmouseout="this.style.background='#f97316'">
            ➕ Publicar receta
          </a>
        `;
      }
      renderUserMenu(sessionArea, data.user);
    } else {
      if (publishArea) publishArea.innerHTML = '';
      renderGuestMenu(sessionArea);
    }
  } catch (e) {
    if (publishArea) publishArea.innerHTML = '';
    console.warn("Cargando modo invitado por fallo de red o sesión inactiva.");
    renderGuestMenu(sessionArea);
  }
}

function renderGuestMenu(container) {
  container.innerHTML = `
    <button onclick="toggleUserMenu(event)" style="background: none; border: none; cursor: pointer; display: flex; align-items: center;">
      <div style="width: 35px; height: 35px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; border: 1px solid #d1d5db;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
      </div>
    </button>

    <div id="user-dropdown" style="display: none; position: absolute; right: 0; top: 45px; background: white; min-width: 200px; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 1000; padding: 12px;">
      <p style="margin: 0 0 12px 0; font-size: 0.85rem; color: #6b7280; font-weight: 500; text-align: center;">Bienvenido, Invitado</p>
      <div style="display: flex; flex-direction: column; gap: 8px;">
        <a href="login.html" style="display: block; text-align: center; background: #f97316; color: white; padding: 8px; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">Iniciar sesión</a>
        <a href="register.html" style="display: block; text-align: center; border: 1px solid #f97316; color: #f97316; padding: 8px; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">Registrarse</a>
      </div>
    </div>
  `;
}

function renderUserMenu(container, user) {
  const esAdmin = user.rol === 'admin';
  const tagRol = esAdmin ? '<span style="background: #ef4444; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-bottom: 4px; display: inline-block;">Admin</span>' : '';

  container.innerHTML = `
    <button onclick="toggleUserMenu(event)" style="background: none; border: none; cursor: pointer; display: flex; align-items: center;">
      <img src="media/pfp/1.png" alt="Perfil" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #f97316;">
    </button>

    <div id="user-dropdown" style="display: none; position: absolute; right: 0; top: 45px; background: white; min-width: 220px; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 1000; overflow: hidden;">
      
      <div style="padding: 16px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 6px;">
        <img src="media/pfp/1.png" alt="Perfil" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #f97316;">
        <div>
          <div style="font-size: 0.75rem; color: #6b7280; font-weight: 500;">Bienvenido</div>
          ${tagRol}
          <div style="font-weight: bold; color: #111827; font-size: 0.95rem;">${user.nombre}</div>
        </div>
      </div>

      <div style="padding: 6px 0;">
        <a href="profile.html" style="display: flex; align-items: center; padding: 10px 16px; color: #374151; text-decoration: none; font-size: 0.9rem; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background=''">
          <span style="margin-right: 8px;">ℹ️</span> Más información
        </a>
        <a href="#" onclick="logout(event)" style="display: flex; align-items: center; padding: 10px 16px; color: #dc2626; text-decoration: none; font-size: 0.9rem; border-top: 1px solid #f3f4f6; transition: background 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background=''">
          <span style="margin-right: 8px;">🚪</span> Cerrar sesión
        </a>
      </div>
    </div>
  `;
}

function toggleUserMenu(event) {
  if (event) event.stopPropagation();
  const dropdown = document.getElementById('user-dropdown');
  if (dropdown) {
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  }
}

document.addEventListener('click', () => {
  const dropdown = document.getElementById('user-dropdown');
  if (dropdown) dropdown.style.display = 'none';
});

async function logout(event) {
  if (event) event.preventDefault();
  try {
    const res = await fetch('auth/logout.php');
    const data = await res.json();
    if (data.success) window.location.href = 'index.html';
  } catch (e) {
    console.error("Error al cerrar sesión", e);
    window.location.reload();
  }
}
