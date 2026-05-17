// header.js

// 1. Buscamos el archivo HTML del menú
fetch('header.html')
  .then(response => {
    if (!response.ok) throw new Error("No se pudo obtener header.html");
    return response.text();
  })
  .then(data => {
    // 2. Lo inyectamos en el contenedor <div id="header"></div> de la página actual
    const headerContainer = document.getElementById('header');
    if (headerContainer) {
      headerContainer.innerHTML = data;
    }

    // 3. PASO CLAVE: Ahora que el contenedor '#nav-session-actions' existe, iniciamos la sesión
    if (typeof checkSession === 'function') {
      checkSession();
    }
  })
  .catch(err => console.error("Error cargando el sistema de navegación:", err));