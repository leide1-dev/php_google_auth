document.addEventListener('DOMContentLoaded', function() {
    // Lógica del Popup
    const popup = document.getElementById('popup-bienvenida');
    const aceptarBtn = document.getElementById('aceptar-popup');

    // Comprobamos si el popup ya se mostró en esta sesión del navegador
    // para evitar que aparezca en cada recarga de la página.
    if (!sessionStorage.getItem('popupMostrado')) {
        // --- LÍNEA CORREGIDA ---
        // Usamos 'flex' para que las reglas de centrado del CSS funcionen
        popup.style.display = 'flex';
        
        // Marcamos que ya se mostró
        sessionStorage.setItem('popupMostrado', 'true');
    }

    aceptarBtn.addEventListener('click', () => {
        popup.style.display = 'none';
    });

    // Lógica del temporizador
    const timerElement = document.getElementById('timer');
    if (timerElement) {
        let tiempo = timerElement.textContent.split(':');
        let segundos = parseInt(tiempo[0]) * 3600 + parseInt(tiempo[1]) * 60 + parseInt(tiempo[2]);

        setInterval(() => {
            segundos++;
            let h = Math.floor(segundos / 3600).toString().padStart(2, '0');
            let m = Math.floor((segundos % 3600) / 60).toString().padStart(2, '0');
            let s = (segundos % 60).toString().padStart(2, '0');
            timerElement.textContent = `${h}:${m}:${s}`;
        }, 1000);
    }
});

// Limpiar el sessionStorage al cerrar sesión para que el popup vuelva a aparecer
document.querySelector('.logout-btn')?.addEventListener('click', () => {
    sessionStorage.removeItem('popupMostrado');
});