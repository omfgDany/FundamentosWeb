// publish.js

document.addEventListener("DOMContentLoaded", async () => {
  // 1. Proteger la vista: Asegurarse de que el usuario esté logueado antes de escribir una receta
  try {
    const sessionRes = await fetch('auth/session_check.php');
    const sessionData = await sessionRes.json();
    
    if (!sessionData.loggedIn) {
      window.location.href = 'login.html';
      return;
    }
  } catch (err) {
    window.location.href = 'login.html';
    return;
  }

  // 2. Previsualización de la imagen seleccionada
  const imageInput = document.getElementById('imagen');
  const imagePreview = document.getElementById('image-preview');

  imageInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        imagePreview.src = e.target.result;
        imagePreview.style.display = 'block';
      }
      reader.readAsDataURL(file);
    } else {
      imagePreview.style.display = 'none';
    }
  });

  // 3. Envío del Formulario vía Fetch (Multipart/form-data)
  const form = document.getElementById('recipe-form');
  const responseMsg = document.getElementById('response-message');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    responseMsg.style.display = 'none';
    responseMsg.style.background = '#f3f4f6';
    responseMsg.style.color = '#1f2937';
    responseMsg.textContent = 'Procesando y subiendo receta...';
    responseMsg.style.display = 'block';

    // Capturamos todos los datos (incluyendo el archivo binario de la imagen)
    const formData = new FormData(form);

    try {
      const response = await fetch('auth/publish_recipe.php', {
        method: 'POST',
        body: formData // Al enviar un FormData, el navegador asigna automáticamente el Content-Type correcto
      });

      const result = await response.json();

      if (result.success) {
        responseMsg.style.background = '#d1fae5';
        responseMsg.style.color = '#065f46';
        responseMsg.textContent = '🎉 ¡Receta publicada exitosamente! Redirigiendo...';
        form.reset();
        imagePreview.style.display = 'none';
        
        setTimeout(() => {
          window.location.href = 'community.html';
        }, 2000);
      } else {
        responseMsg.style.background = '#fee2e2';
        responseMsg.style.color = '#991b1b';
        responseMsg.textContent = `Error: ${result.error}`;
      }
    } catch (error) {
      console.error(error);
      responseMsg.style.background = '#fee2e2';
      responseMsg.style.color = '#991b1b';
      responseMsg.textContent = 'Hubo un fallo de red al intentar conectar con el servidor.';
    }
  });
});