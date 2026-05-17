document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("main-search");
  const button = document.getElementById("main-search-btn");
  const error = document.getElementById("main-search-error");

  if (!input || !button || !error) return;

  function normalizar(texto) {
    return String(texto || "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .toLowerCase()
      .trim();
  }

  function recetaCoincide(receta, busqueda) {
    const campos = [
      receta.titulo,
      receta.categoria,
      receta.descripcion,
      receta.autor,
      receta.ingredientes
    ];

    return campos.some((campo) => normalizar(campo).includes(busqueda));
  }

  async function buscarReceta() {
    const busqueda = normalizar(input.value);
    error.textContent = "";

    if (!busqueda) {
      error.textContent = "Escribe el nombre de una receta para buscar.";
      input.focus();
      return;
    }

    try {
      button.disabled = true;
      const response = await fetch("auth/recetas_api.php");
      const recetas = await response.json();

      if (recetas.error) {
        error.textContent = "No se pudo buscar en este momento.";
        return;
      }

      const resultado = recetas.find((receta) => recetaCoincide(receta, busqueda));

      if (resultado) {
        window.location.href = `recipe-detail.html?id=${resultado.id}`;
        return;
      }

      error.textContent = "No se encontró tu busqueda :( prueba con otra receta! :)";
    } catch (err) {
      error.textContent = "No se pudo buscar en este momento.";
    } finally {
      button.disabled = false;
    }
  }

  button.addEventListener("click", buscarReceta);
  input.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
      buscarReceta();
    }
  });
});
