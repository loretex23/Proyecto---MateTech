// Abrir y cerrar el menú en móvil
        const btn = document.getElementById('btnMenu');
        const menu = document.getElementById('menuNavegacion');

        btn.addEventListener('click', () => {
            menu.classList.toggle('abierto');
            btn.classList.toggle('abierto');
        });