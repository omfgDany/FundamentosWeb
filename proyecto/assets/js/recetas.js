function nombreMes(mes) {
  const meses = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
  return meses[Number(mes)] || "";
}

function fechaReceta(receta) {
  return new Date(Number(receta.anio), Number(receta.mes) - 1, Number(receta.dia)).getTime();
}

function escapeHtml(texto) {
  const div = document.createElement("div");
  div.textContent = texto ?? "";
  return div.innerHTML;
}

function crearCard(receta) {
  const fechaStr = `${nombreMes(receta.mes)} ${receta.dia}, ${receta.anio}`;
  const titulo = escapeHtml(receta.titulo);
  const autor = escapeHtml(receta.autor);
  const categoria = escapeHtml(receta.categoria);
  const descripcion = escapeHtml(receta.descripcion);
  const imagen = escapeHtml(receta.imagen);

  return `
    <a href="recipe-detail.html?id=${receta.id}" class="card" tabindex="0">
      <img class="card-img" src="${imagen}" alt="${titulo}">
      <div class="card-body">
        <div class="card-tags">
          <span class="badge">${categoria}</span>
        </div>
        <h3 class="card-title">${titulo}</h3>
        <p class="card-desc">${descripcion}</p>
        <div class="card-meta">
          <div class="card-author">
            <em style="color:var(--muted);font-size:0.95em">${fechaStr}</em>
            <img class="author-avatar" src="${escapeHtml(receta.avatar)}" alt="${autor}">
            <span class="author-name">${autor}</span>
          </div>
          <div class="card-stats">
            <span class="stat"><i class="fa-solid fa-heart" style="color:#f97316;"></i> ${receta.likes}</span>
            <span class="stat"><i class="fa-regular fa-comment"></i> ${receta.comentarios}</span>
          </div>
        </div>
      </div>
    </a>
  `;
}

function ordenarRecetas(recetas, modo) {
  const ordenadas = [...recetas];

  if (modo === "alfabetico") {
    return ordenadas.sort((a, b) => a.titulo.localeCompare(b.titulo, "es", { sensitivity: "base" }));
  }

  if (modo === "popular") {
    return ordenadas.sort((a, b) => Number(b.likes) - Number(a.likes));
  }

  return ordenadas.sort((a, b) => fechaReceta(b) - fechaReceta(a));
}

function cargarHome(recetas) {
  const featuredGrid = document.getElementById("featured-grid");
  if (featuredGrid) {
    featuredGrid.innerHTML = ordenarRecetas(recetas, "fecha").map(crearCard).join("");
  }

  const trendingGrid = document.getElementById("trending-grid");
  if (trendingGrid) {
    trendingGrid.innerHTML = ordenarRecetas(recetas, "popular").map(crearCard).join("");
  }
}

function inicializarComunidad(recetas) {
  const comunidadGrid = document.getElementById("community-grid");
  if (!comunidadGrid) return;

  const estado = {
    categoria: "all",
    orden: "alfabetico"
  };

  const categoryRow = document.getElementById("category-row");
  const resultCount = document.getElementById("result-count");
  const emptyState = document.getElementById("community-empty");
  const clearBtn = document.getElementById("clear-filters");
  const sortButtons = document.querySelectorAll("[data-sort]");

  function categoriasDisponibles() {
    return [...new Set(recetas.map((receta) => receta.categoria).filter(Boolean))]
      .sort((a, b) => a.localeCompare(b, "es", { sensitivity: "base" }));
  }

  function pintarCategorias() {
    if (!categoryRow) return;

    const botones = [
      '<button class="btn btn-sm btn-outline active" data-category="all">Todas</button>',
      ...categoriasDisponibles().map((categoria) => (
        `<button class="btn btn-sm btn-outline" data-category="${encodeURIComponent(categoria)}">${escapeHtml(categoria)}</button>`
      ))
    ];

    categoryRow.innerHTML = botones.join("");
  }

  function pintarEstadoActivo() {
    document.querySelectorAll("[data-category]").forEach((btn) => {
      btn.classList.toggle("active", btn.dataset.category === encodeURIComponent(estado.categoria));
    });

    sortButtons.forEach((btn) => {
      btn.classList.toggle("active", btn.dataset.sort === estado.orden);
    });
  }

  function recetasFiltradas() {
    const filtradas = estado.categoria === "all"
      ? recetas
      : recetas.filter((receta) => receta.categoria === estado.categoria);

    return ordenarRecetas(filtradas, estado.orden);
  }

  function actualizarVista() {
    const visibles = recetasFiltradas();

    comunidadGrid.innerHTML = visibles.map(crearCard).join("");
    if (resultCount) {
      resultCount.textContent = `Mostrando ${visibles.length} ${visibles.length === 1 ? "receta" : "recetas"}`;
    }

    if (emptyState) {
      emptyState.style.display = visibles.length ? "none" : "block";
    }

    pintarEstadoActivo();
  }

  pintarCategorias();
  actualizarVista();

  if (categoryRow) {
    categoryRow.addEventListener("click", (event) => {
      const btn = event.target.closest("[data-category]");
      if (!btn) return;
      estado.categoria = btn.dataset.category === "all" ? "all" : decodeURIComponent(btn.dataset.category);
      actualizarVista();
    });
  }

  sortButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      estado.orden = btn.dataset.sort;
      actualizarVista();
    });
  });

  if (clearBtn) {
    clearBtn.addEventListener("click", () => {
      estado.categoria = "all";
      estado.orden = "alfabetico";
      actualizarVista();
    });
  }
}

function cargarRecetas() {
  fetch("auth/recetas_api.php")
    .then((res) => res.json())
    .then((recetas) => {
      if (recetas.error) {
        console.error("Error desde el API:", recetas.error);
        return;
      }

      cargarHome(recetas);
      inicializarComunidad(recetas);
    })
    .catch((err) => console.error("Error al obtener recetas de la BD:", err));
}

document.addEventListener("DOMContentLoaded", cargarRecetas);
