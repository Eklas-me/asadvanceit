<!-- HTML -->
<div class="spreadsheet-wrapper">
  <button id="fullscreenBtn">⛶</button>
  <div class="spreadsheet-container">
    <iframe id="mySpreadsheet"
      src="https://docs.google.com/spreadsheets/d/1-eqhWV3Ke9QbU2c_wTRxbKwW8m54uSnLvq1tD59IjeA/edit?usp=sharing"
      frameborder="0" scrolling="no">
    </iframe>
  </div>
</div>

<!-- CSS -->
<style>
.spreadsheet-wrapper {
  position: relative;
  max-width: 1200px;
  margin: 20px auto;
}

.spreadsheet-container {
  width: 100%;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.spreadsheet-container iframe {
  width: 100%;
  height: 80vh;
  /* Normal height */
  border: none;
  overflow: hidden;
  transition: height 0.3s ease;
  /* Smooth height transition */
}

#fullscreenBtn {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 10px;
  cursor: pointer;
  font-size: 18px;
}

#fullscreenBtn:hover {
  background: rgba(0, 0, 0, 0.8);
}
</style>

<!-- JS -->
<script>
const btn = document.getElementById('fullscreenBtn');
const container = document.querySelector('.spreadsheet-container');
const iframe = document.getElementById('mySpreadsheet');

btn.addEventListener('click', () => {
  if (!document.fullscreenElement) {
    // Enter fullscreen
    container.requestFullscreen().then(() => {
      iframe.style.height = '100vh'; // Fullscreen height
    }).catch(err => {
      alert(`Error attempting to enable fullscreen: ${err.message}`);
    });
  } else {
    // Exit fullscreen
    document.exitFullscreen().then(() => {
      iframe.style.height = '80vh'; // Normal height
    });
  }
});
</script>