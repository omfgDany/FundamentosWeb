// header.js
// Incluye el header.html en el elemento <div id="header"></div> de cada página
fetch('header.html')
  .then(response => response.text())
  .then(data => {
    document.getElementById('header').innerHTML = data;
  });
