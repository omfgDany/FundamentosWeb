// Lógica para el chat con IA

document.addEventListener('DOMContentLoaded', function() {
  const input = document.getElementById('ai-input');
  const submitBtn = document.getElementById('ai-submit');
  const responseDiv = document.getElementById('ai-response');
  const container = document.querySelector('.container');

  // Crear contenedor de chat (inicialmente oculto)
  let chatView = document.createElement('div');
  chatView.id = 'ai-chat-view';
  chatView.style.maxWidth = '600px';
  chatView.style.margin = '0 auto';
  chatView.style.background = 'var(--light)';
  chatView.style.padding = '16px';
  chatView.style.borderRadius = '4px';
  chatView.style.boxShadow = '0 2px 6px rgba(0,0,0,0.1)';
  chatView.style.display = 'none';
  chatView.innerHTML = `
    <div id="chat-messages" style="min-height:120px;"></div>
    <div style="display:flex;align-items:center;margin-top:12px;">
      <input id="chat-input" type="text" placeholder="Escribe tu pregunta..." style="flex:1;padding:12px 16px;border:1px solid var(--muted);border-radius:4px">
      <button id="chat-send" class="bienvenida-search-btn" style="margin-left:8px;padding:12px 16px">
        <i class="fa-solid fa-paper-plane"></i>
      </button>
    </div>
  `;
  container.appendChild(chatView);

  // Animación de tres puntos
  function startLoadingAnimation(btn) {
    btn.innerHTML = `<span class="dot-anim">●●●</span>`;
    btn.disabled = true;
    let i = 0;
    const colors = ['#f97316', '#8b5cf6', '#b26a1a'];
    const dots = btn.querySelector('.dot-anim');
    btn._interval = setInterval(() => {
      dots.style.color = colors[i % colors.length];
      i++;
    }, 350);
  }
  function stopLoadingAnimation(btn, iconClass = 'fa-paper-plane') {
    if (btn._interval) clearInterval(btn._interval);
    btn.innerHTML = `<i class="fa-solid ${iconClass}"></i>`;
    btn.disabled = false;
  }

  // Simulación de llamada a la IA (reemplaza por fetch real a tu API)
  function fakeIARequest(question) {
    return new Promise(resolve => {
      setTimeout(() => {
        resolve('Respuesta de la IA a: ' + question);
      }, 2000);
    });
  }

  // Cambia a la vista de chat y envía pregunta
  submitBtn.addEventListener('click', async function() {
    const question = input.value.trim();
    if (!question) return;
    // Oculta la vista de bienvenida y muestra el chat
    container.querySelector('.bienvenida-search-row').style.display = 'none';
    responseDiv.style.display = 'none';
    chatView.style.display = 'block';
    // Muestra la pregunta en el chat
    const chatMessages = document.getElementById('chat-messages');
    chatMessages.innerHTML = `<div style='margin-bottom:8px'><b>Tú:</b> ${question}</div>`;
    // Pone la pregunta en el input del chat
    document.getElementById('chat-input').value = question;
    // Cambia el botón por animación
    const chatSend = document.getElementById('chat-send');
    startLoadingAnimation(chatSend);
    // Espera respuesta de la IA
    const answer = await fakeIARequest(question);
    // Muestra respuesta
    chatMessages.innerHTML += `<div style='margin-bottom:8px'><b>IA:</b> ${answer}</div>`;
    stopLoadingAnimation(chatSend);
    document.getElementById('chat-input').value = '';
  });

  // Permite enviar desde el chat
  container.addEventListener('click', async function(e) {
    if (e.target.closest('#chat-send')) {
      const chatInput = document.getElementById('chat-input');
      const chatMessages = document.getElementById('chat-messages');
      const chatSend = document.getElementById('chat-send');
      const question = chatInput.value.trim();
      if (!question) return;
      chatMessages.innerHTML += `<div style='margin-bottom:8px'><b>Tú:</b> ${question}</div>`;
      startLoadingAnimation(chatSend);
      const answer = await fakeIARequest(question);
      chatMessages.innerHTML += `<div style='margin-bottom:8px'><b>IA:</b> ${answer}</div>`;
      stopLoadingAnimation(chatSend);
      chatInput.value = '';
    }
  });
});
