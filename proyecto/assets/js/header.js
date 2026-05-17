fetch('partials/header.html')
  .then(response => {
    if (!response.ok) throw new Error("No se pudo obtener el header");
    return response.text();
  })
  .then(data => {
    const headerContainer = document.getElementById('header');
    if (headerContainer) {
      headerContainer.innerHTML = data;
    }

    if (typeof checkSession === 'function') {
      checkSession();
    }
  })
  .catch(err => console.error("Error cargando la navegación:", err));
