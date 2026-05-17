document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("chat-form");
  const input = document.getElementById("chat-input");
  const sendBtn = document.getElementById("chat-send");
  const messages = document.getElementById("chat-messages");

  if (!form || !input || !sendBtn || !messages) return;

  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  function pintarMensajes(historial) {
    if (!historial.length) {
      messages.innerHTML = `
        <div class="msg msg-bot">
          Hola, soy tu asistente de cocina. Dime qué ingredientes tienes o qué receta quieres preparar.
        </div>
      `;
      return;
    }

    messages.innerHTML = historial.map((mensaje) => {
      const clase = mensaje.autor === "usuario" ? "msg-user" : "msg-bot";
      return `<div class="msg ${clase}">${escapeHtml(mensaje.texto)}</div>`;
    }).join("");

    messages.scrollTop = messages.scrollHeight;
  }

  function setLoading(loading) {
    sendBtn.disabled = loading;
    input.disabled = loading;
    sendBtn.innerHTML = loading
      ? '<span class="dot-anim">...</span>'
      : '<i class="fa-solid fa-paper-plane"></i>';
  }

  async function obtenerHistorial() {
    const respuesta = await fetch("auth/ai_api.php");
    const data = await respuesta.json();
    pintarMensajes(data.messages || []);
  }

  async function enviarPregunta(texto) {
    setLoading(true);

    try {
      const respuesta = await fetch("auth/ai_api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: texto })
      });

      const data = await respuesta.json();
      if (!data.success) {
        pintarMensajes([
          { autor: "bot", texto: data.error || "No pude responder en este momento." }
        ]);
        return;
      }

      pintarMensajes(data.messages || []);
    } catch (error) {
      pintarMensajes([
        { autor: "bot", texto: "No se pudo conectar con el chat. Revisa que Apache/PHP esté activo." }
      ]);
    } finally {
      setLoading(false);
      input.focus();
    }
  }

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const texto = input.value.trim();
    if (!texto) return;

    input.value = "";
    enviarPregunta(texto);
  });

  obtenerHistorial();
});
