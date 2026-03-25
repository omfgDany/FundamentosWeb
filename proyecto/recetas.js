// recetas.js
// Carga y muestra recetas en index.html y community.html según el orden solicitado


function nombreMes(mes) {
  // mes: 1-12
  const meses = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
  return meses[mes];
}

function crearCard(receta) {
  const fechaStr = `<em style='color:var(--muted);font-size:0.95em'>${nombreMes(receta.mes)} ${receta.dia}, ${receta.anio}</em>`;
  return `
    <div class="card" tabindex="0" style="cursor:pointer">
      <img class="card-img" src="${receta.imagen}" alt="${receta.titulo}">
      <div class="card-body">
        <div class="card-tags">
          <span class="badge">${receta.categoria}</span>
          <span class="badge">${receta.dificultad}</span>
        </div>
        <h3 class="card-title">${receta.titulo}</h3>
        <p class="card-desc">${receta.descripcion}</p>
        <div class="card-meta">
          <div class="card-author">
            ${fechaStr} <img class="author-avatar" src="${receta.avatar}" alt="Chef">
            <span class="author-name">${receta.autor}</span>
          </div>
          <div class="card-stats">
            <span class="stat"><i class='fa-solid fa-heart' style='color:#f97316;'></i> ${receta.likes}</span>
            <span class="stat"><i class='fa-regular fa-comment'></i> ${receta.comentarios}</span>
          </div>
        </div>
      </div>
    </div>
  `;
}

function cargarRecetas() {
  fetch('recetas.json')
    .then(res => res.json())
    .then(recetas => {
      // Destacadas (index): por fecha descendente
      const destacadas = [...recetas].sort((a, b) => {
        // Ordenar por año, luego mes, luego día
        if (b.anio !== a.anio) return b.anio - a.anio;
        if (b.mes !== a.mes) return b.mes - a.mes;
        return b.dia - a.dia;
      });
      const featuredGrid = document.getElementById('featured-grid');
      if (featuredGrid) {
        featuredGrid.innerHTML = destacadas.map(crearCard).join('');
      }
      // Tendencias (populares): por likes descendente
      const trendingGrid = document.getElementById('trending-grid');
      if (trendingGrid) {
        const populares = [...recetas].sort((a, b) => b.likes - a.likes);
        trendingGrid.innerHTML = populares.map(crearCard).join('');
      }
      // Comunidad: por nombre ascendente por defecto
      const comunidadGrid = document.getElementById('community-grid');
      if (comunidadGrid) {
        mostrarRecetasComunidad(recetas, 'alfabetico');
        agregarSortListeners(recetas);
      }
    });
}


function mostrarRecetasComunidad(recetas, modo) {
  let ordenadas = [...recetas];
  if (modo === 'alfabetico') {
    ordenadas.sort((a, b) => a.titulo.localeCompare(b.titulo));
  } else if (modo === 'popular') {
    ordenadas.sort((a, b) => b.likes - a.likes);
  } else if (modo === 'fecha') {
    ordenadas.sort((a, b) => {
      if (b.anio !== a.anio) return b.anio - a.anio;
      if (b.mes !== a.mes) return b.mes - a.mes;
      return b.dia - a.dia;
    });
  }
  const comunidadGrid = document.getElementById('community-grid');
  if (comunidadGrid) {
    comunidadGrid.innerHTML = ordenadas.map(crearCard).join('');
  }
}

function agregarSortListeners(recetas) {
  const btnAlf = document.getElementById('sort-alfabetico');
  const btnPop = document.getElementById('sort-popular');
  const btnFecha = document.getElementById('sort-fecha');
  if (btnAlf) btnAlf.onclick = () => mostrarRecetasComunidad(recetas, 'alfabetico');
  if (btnPop) btnPop.onclick = () => mostrarRecetasComunidad(recetas, 'popular');
  if (btnFecha) btnFecha.onclick = () => mostrarRecetasComunidad(recetas, 'fecha');
}

document.addEventListener('DOMContentLoaded', cargarRecetas);


