<?php
session_start();
session_unset(); // Rimuove tutte le variabili di sessione
session_destroy(); // Distrugge la sessione attiva
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Sound Sanctuary | Studio Musicale</title>
  <style>
    /* Sfondo gradiente chiaro */
    body {
      background: linear-gradient(135deg, #d0e8f2, #f7fafc);
      color: #333;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      overflow-x: hidden;
      position: relative;
      
      /* Centrare verticalmente e orizzontalmente */
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
    }
    .fade-out {
      opacity: 0;
      transition: opacity 0.6s ease;
    }


    /* Contenitore testo centrale */
    .content {
      position: relative;
      z-index: 10;
      max-width: 600px;
      background: rgba(255 255 255 / 0.9);
      padding: 50px 40px;
      border-radius: 12px;
      box-shadow:
        0 0 25px rgba(58, 114, 255, 0.4),
        0 8px 25px rgb(0 0 0 / 0.12);
      user-select: none;
    }

    h1 {
      font-size: 3.5em;
      margin: 0 0 20px;
      color: #1a3d7c;
    }

    .description {
      font-size: 1.2em;
      margin-bottom: 30px;
      color: #555;
    }

    a {
      text-decoration: none;
    }

    .enter-button {
      background-color: #3a72ff;
      color: white;
      padding: 14px 36px;
      border: none;
      border-radius: 6px;
      font-size: 1.25em;
      cursor: pointer;
      transition: background-color 0.3s ease, box-shadow 0.6s ease;
      box-shadow:
        0 6px 15px rgb(58 114 255 / 0.8);
      user-select: none;
    }

    .enter-button:hover {
      background-color: #1f4ede;
      box-shadow:
        0 10px 30px rgb(31 78 222 / 1);
      animation: pulse 2s infinite;
    }

    /* Pulsazione dolce per bottone */
    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
        box-shadow: 0 10px 30px rgb(31 78 222 / 1);
      }
      50% {
        transform: scale(1.05);
        box-shadow: 0 14px 40px rgb(31 78 222 / 0.9);
      }
    }

    /* Contenitore note musicali animati */
    .notes-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      pointer-events: none;
      overflow: visible;
      z-index: 1;
      user-select: none;
    }

    /* Note singole - più grandi e più marcate */
    .note {
  position: absolute;
  font-size: 2.5em;
  color: rgba(30, 60, 130, 0.9); /* blu più scuro, quasi pieno */
  animation-timing-function: linear;
  animation-iteration-count: infinite;
  will-change: transform;
  filter: drop-shadow(0 0 6px rgba(20, 40, 100, 0.6)); /* ombra più profonda */
}

    /* Animazioni diverse per note */
    @keyframes moveUp {
      0% {
        transform: translateY(100vh) translateX(0) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 1;
      }
      100% {
        transform: translateY(-10vh) translateX(50px) rotate(360deg);
        opacity: 0;
      }
    }

    @keyframes moveUpLeft {
      0% {
        transform: translateY(100vh) translateX(0) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 1;
      }
      100% {
        transform: translateY(-10vh) translateX(-50px) rotate(-360deg);
        opacity: 0;
      }
    }

    /* Sfumature morbide di luce (bokeh) */
    .bokeh {
      position: fixed;
      width: 100vw;
      height: 100vh;
      top: 0; left: 0;
      pointer-events: none;
      overflow: hidden;
      z-index: 0;
      user-select: none;
    }

    .bokeh-circle {
      position: absolute;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
      animation-timing-function: ease-in-out;
      filter: blur(30px);
      opacity: 0.3;
    }

    @keyframes moveBokeh {
      0% {
        transform: translate(0, 0) scale(1);
        opacity: 0.3;
      }
      50% {
        transform: translate(20px, -15px) scale(1.1);
        opacity: 0.5;
      }
      100% {
        transform: translate(0, 0) scale(1);
        opacity: 0.3;
      }
    }
  </style>
</head>
<body>

  <div class="notes-container" aria-hidden="true">
    <!-- Note generate da JS -->
  </div>

  <div class="bokeh" aria-hidden="true">
    <!-- Sfumature luce generate da JS -->
  </div>

  <div class="content" role="main" aria-label="Benvenuto Sound Sanctuary">
    <h1>Sound Sanctuary</h1>
    <p class="description">
      Il tuo rifugio musicale dove prenotare sale di registrazione, scoprire servizi esclusivi e vivere la musica in tutte le sue sfumature.
    </p>
    <a href="principale.php">
      <button class="enter-button" aria-label="Entra nello studio musicale">Entra ora</button>
    </a>
  </div>

<script>
  // Note musicali animate fuori dalla zona centrale
  const notesContainer = document.querySelector('.notes-container');
  const numNotes = 45;
  const notesSymbols = ['♪', '♫', '♬', '♩', '♭'];

  function randomRange(min, max) {
    return Math.random() * (max - min) + min;
  }

  for(let i = 0; i < numNotes; i++) {
    const note = document.createElement('div');
    note.classList.add('note');
    note.textContent = notesSymbols[Math.floor(Math.random() * notesSymbols.length)];

    // dimensione variabile tra 2.0em e 3.5em (più grandi)
    const size = randomRange(3.0, 5.5);
    note.style.fontSize = size + 'em';

    let posX, posY;
    do {
      posX = randomRange(0, window.innerWidth);
      posY = randomRange(0, window.innerHeight);
    } while (
      posX > window.innerWidth * 0.25 &&
      posX < window.innerWidth * 0.75 &&
      posY > window.innerHeight * 0.20 &&
      posY < window.innerHeight * 0.80
    );

    note.style.left = posX + 'px';
    note.style.top = posY + 'px';

    const anim = Math.random() > 0.5 ? 'moveUp' : 'moveUpLeft';
    const duration = randomRange(12, 22);
    note.style.animation = `${anim} ${duration}s linear infinite`;

    notesContainer.appendChild(note);
  }

  // Sfumature di luce (bokeh) animate
  const bokehContainer = document.querySelector('.bokeh');
  const numCircles = 8;
  for (let i = 0; i < numCircles; i++) {
    const circle = document.createElement('div');
    circle.classList.add('bokeh-circle');

    const size = 100 + Math.random() * 200;
    circle.style.width = size + 'px';
    circle.style.height = size + 'px';

    circle.style.left = (Math.random() * 100) + 'vw';
    circle.style.top = (Math.random() * 100) + 'vh';

    const duration = 40 + Math.random() * 40;
    circle.style.animation = `moveBokeh ${duration}s ease-in-out infinite alternate`;

    circle.style.animationDelay = (Math.random() * duration) + 's';

    bokehContainer.appendChild(circle);
  }
</script>

</body>
</html>




