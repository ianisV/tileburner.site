// ============================================
// CONFIGURATION ET CHEMINS
// ============================================
const IS_IN_PHP_FOLDER = window.location.pathname.includes('/php/');
const PATH_PREFIX = IS_IN_PHP_FOLDER ? '../' : '';

const AUDIO_CONFIG = {
    musiqueFond: PATH_PREFIX + 'audio/musique_fond.mp3',
    musiqueJeu:  PATH_PREFIX + 'audio/musique_jeu.mp3',
    sons: {
        clic:  PATH_PREFIX + 'audio/clic_bouton.mp3',
        hover: PATH_PREFIX + 'audio/hover_bouton.mp3',
    }
};

// Détection : est-on sur la page de jeu ?
const EST_PAGE_JEU = window.location.pathname.endsWith('jeu.html');

const bgMusic = new Audio(AUDIO_CONFIG.musiqueFond);
bgMusic.loop = true;

const gameMusic = new Audio(AUDIO_CONFIG.musiqueJeu);
gameMusic.loop = true;

// Musique active selon la page
const musiqueActive = EST_PAGE_JEU ? gameMusic : bgMusic;

// ============================================
// VOLUME
// ============================================
function refreshSettings() {
    const master = parseInt(localStorage.getItem('vol_master') || '50') / 100;
    const music  = parseInt(localStorage.getItem('vol_music')  || '50') / 100;
    const isMuted = localStorage.getItem('musicMuted') === 'true';
    const volume = Math.max(0, Math.min(1, master * music));

    bgMusic.muted = isMuted;
    bgMusic.volume = volume;

    gameMusic.muted = isMuted;
    gameMusic.volume = volume;
}

function obtenirVolumeSFX() {
    const master = parseInt(localStorage.getItem('vol_master') || '50') / 100;
    const sfx    = parseInt(localStorage.getItem('vol_sfx')    || '50') / 100;
    const isMuted = localStorage.getItem('musicMuted') === 'true';
    return isMuted ? 0 : Math.max(0, Math.min(1, master * sfx));
}

// ============================================
// SONS
// ============================================
function jouerSon(nomSon, callback = null) {
    const vol = obtenirVolumeSFX();
    const son = new Audio(AUDIO_CONFIG.sons[nomSon]);
    son.volume = vol;

    if (callback) {
        const navigate = () => callback();
        const p = son.play();
        let done = false;
        const goOnce = () => { if (!done) { done = true; navigate(); } };

        if (p && typeof p.then === 'function') {
            p.then(() => {
                son.addEventListener('ended', goOnce);
                setTimeout(goOnce, 250);
            }).catch(() => {
                navigate();
            });
        } else {
            setTimeout(goOnce, 200);
        }
    } else {
        son.play().catch(() => {});
    }
}

// ============================================
// REPRISE DE LA MUSIQUE ENTRE LES PAGES
// ============================================
function tenterReprise() {
    if (EST_PAGE_JEU) {
        // Sur la page de jeu : on démarre la musique de jeu depuis le début
        // (pas de reprise de position, c'est une musique différente)
        if (localStorage.getItem('musicMuted') !== 'true' &&
            localStorage.getItem('musicPlaying') === 'true') {
            gameMusic.play().catch(() => {});
        }
    } else {
        // Sur les autres pages : musique de menu avec reprise de position
        const savedTime = parseFloat(localStorage.getItem('musicTime') || '0');
        if (savedTime > 0) bgMusic.currentTime = savedTime;

        if (localStorage.getItem('musicMuted') !== 'true' &&
            localStorage.getItem('musicPlaying') === 'true') {
            bgMusic.play().catch(() => {});
        }
    }
}

// ============================================
// ÉVÉNEMENTS GLOBAUX
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    refreshSettings();
    tenterReprise();

    // Filet de sécurité : si l'autoplay a été bloqué, on relance au premier clic
    document.addEventListener('click', () => {
        if (localStorage.getItem('musicMuted') === 'true') return;
        if (musiqueActive.paused) {
            localStorage.setItem('musicPlaying', 'true');
            musiqueActive.play().catch(() => {});
        }
    }, { once: true });

    // HOVER
    document.addEventListener('mouseover', (e) => {
        if (e.target.closest('a, button, .btn, input[type="submit"]')) {
            jouerSon('hover');
        }
    });

    // CLIC — interception navigation
    document.addEventListener('click', (e) => {
        if (e.target.matches('input[type="range"], input[type="checkbox"], select, label')) return;

        const lien   = e.target.closest('a');
        const bouton = e.target.closest('button, input[type="submit"]');

        // --- Cas 1 : lien classique ---
        if (lien && lien.href && !lien.hash && lien.target !== "_blank") {
            e.preventDefault();
            const destination = lien.href;
            sauvegarderMusique();
            jouerSon('clic', () => { window.location.href = destination; });
            return;
        }

        // --- Cas 2 : bouton qui fait de la navigation via onclick ---
        if (bouton) {
            const onclickAttr = bouton.getAttribute('onclick') || '';
            const matchHref = onclickAttr.match(/window\.location(?:\.href)?\s*=\s*['"]([^'"]+)['"]/);

            if (matchHref) {
                e.preventDefault();
                e.stopImmediatePropagation();
                bouton.onclick = null;
                const destination = matchHref[1];
                sauvegarderMusique();
                jouerSon('clic', () => { window.location.href = destination; });
                return;
            }

            // --- Cas 3 : bouton submit d'un formulaire ---
            if (bouton.type === 'submit') {
                const form = bouton.closest('form');
                if (form) {
                    e.preventDefault();
                    sauvegarderMusique();
                    jouerSon('clic', () => { form.submit(); });
                    return;
                }
            }

            // --- Cas 4 : bouton normal ---
            jouerSon('clic');
        }
    }, true);
});

// ============================================
// SAUVEGARDE POSITION MUSIQUE
// ============================================
function sauvegarderMusique() {
    // On ne sauvegarde la position que pour la musique du menu
    // (la musique de jeu redémarre à chaque fois)
    if (!EST_PAGE_JEU) {
        localStorage.setItem('musicTime', bgMusic.currentTime);
    }
    localStorage.setItem('musicPlaying', !musiqueActive.paused ? 'true' : 'false');
}

setInterval(() => {
    if (!EST_PAGE_JEU && !bgMusic.paused) {
        localStorage.setItem('musicTime', bgMusic.currentTime);
    }
}, 500);

window.addEventListener('beforeunload', sauvegarderMusique);
window.addEventListener('pagehide', sauvegarderMusique);

window.appliquerVolume = refreshSettings;
