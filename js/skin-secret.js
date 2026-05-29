// =========================================================
// skin-secret.js — Déblocage du skin secret via le logo
// Double-clic sur le logo du header pour activer/désactiver
// =========================================================
(function () {
    const CLE_STORAGE = 'tileburner_skin_secret';
    const DELAI_DOUBLE_CLIC = 400; // ms

    // ---------------------------------------------------------
    // État du skin
    // ---------------------------------------------------------
    function skinDebloque() {
        return localStorage.getItem(CLE_STORAGE) === '1';
    }

    function skinActif() {
        return localStorage.getItem(CLE_STORAGE + '_actif') === '1';
    }

    // Expose globalement pour que les moteurs de jeu puissent lire l'état
    window.SkinSecret = {
        estDebloque: skinDebloque,
        estActif: skinActif,
        activer: () => {
            localStorage.setItem(CLE_STORAGE, '1');
            localStorage.setItem(CLE_STORAGE + '_actif', '1');
        },
        desactiver: () => localStorage.removeItem(CLE_STORAGE + '_actif'),
        toggle: () => {
            if (skinActif()) {
                window.SkinSecret.desactiver();
                return false;
            } else {
                window.SkinSecret.activer();
                return true;
            }
        }
    };

    // ---------------------------------------------------------
    // Notification visuelle
    // ---------------------------------------------------------
    function notifier(msg) {
        const notif = document.createElement('div');
        notif.textContent = msg;
        notif.style.cssText = `
            position: fixed; top: 80px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(180deg, #700000 0%, #3a0000 100%);
            color: #d4af37; padding: 1rem 2rem;
            border: 1px solid #d4af37; border-radius: 4px;
            font-family: 'Cinzel', serif; letter-spacing: 3px;
            text-transform: uppercase; font-size: 0.85rem;
            box-shadow: 0 0 30px rgba(212,175,55,0.6);
            z-index: 9999; animation: fadeSecret 3s forwards;
            pointer-events: none;
        `;
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 3000);
    }

    // ---------------------------------------------------------
    // Styles injectés
    // ---------------------------------------------------------
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeSecret {
            0%   { opacity: 0; transform: translate(-50%, -20px); }
            15%  { opacity: 1; transform: translate(-50%, 0); }
            85%  { opacity: 1; transform: translate(-50%, 0); }
            100% { opacity: 0; transform: translate(-50%, -10px); }
        }
        .nav-logo img {
            cursor: pointer;
            transition: filter 0.2s;
            user-select: none;
            -webkit-user-drag: none;
        }

        .nav-logo img:hover {
            filter: drop-shadow(0 0 8px #d4af37);
        }
        .nav-logo img.skin-secret-actif {
            filter: drop-shadow(0 0 12px #d4af37) hue-rotate(30deg);
        }
    `;
    document.head.appendChild(style);

    // ---------------------------------------------------------
    // Toggle du skin — désactivé pendant une partie
    // ---------------------------------------------------------
    function enPartie() {
        return window.location.pathname.includes('jeu.html')
            || window.location.pathname.includes('concepteur.html');
    }

    function toggleSkin() {
        // Pendant une partie, le logo ne fait rien
        if (enPartie()) return;

        const actifMaintenant = window.SkinSecret.toggle();

        if (actifMaintenant) {
            notifier('✦ Skin secret activé ✦');
        } else {
            notifier('✦ Skin classique activé ✦');
        }

        // Met à jour l'apparence du logo
        const logo = document.querySelector('.nav-logo img');
        if (logo) {
            logo.classList.toggle('skin-secret-actif', actifMaintenant);
        }

    }

    // ---------------------------------------------------------
    // Détection du double-clic sur le logo
    // ---------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        const logo = document.querySelector('.nav-logo img');
        if (!logo) return;

        // Applique l'état visuel actuel
        if (skinActif()) {
            logo.classList.add('skin-secret-actif');
        }

        // Pendant une partie : désactiver visuellement l'interactivité du logo
        if (enPartie()) {
            logo.style.cursor = 'default';
            logo.style.pointerEvents = 'none';
        }

        let dernierClic = 0;
        let timerSimpleClic = null;

        logo.addEventListener('click', (e) => {
            e.preventDefault();
            const maintenant = Date.now();

            if (maintenant - dernierClic < DELAI_DOUBLE_CLIC) {
                // Double-clic détecté
                if (timerSimpleClic) {
                    clearTimeout(timerSimpleClic);
                    timerSimpleClic = null;
                }
                toggleSkin();
                dernierClic = 0;
            } else {
                dernierClic = maintenant;
            }
        });

        // Empêche la sélection texte sur double-clic
        logo.addEventListener('dblclick', (e) => e.preventDefault());
    });

})();