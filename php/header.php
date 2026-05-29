<!-- header.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
?>
<header class="site-header">
    <nav class="nav-left">
        <a href="index.php" class="nav-btn <?= $current === 'index.php' ? 'active' : '' ?>">Accueil</a>
        <a href="profil.php" class="nav-btn <?= $current === 'profil.php' ? 'active' : '' ?>">Profil</a>
        <a href="regles.php" class="nav-btn <?= $current === 'regles.php' ? 'active' : '' ?>">Règles du Jeu</a>
    </nav>

    <div class="nav-logo">
        <img src="../images/logo.png" alt="Logo">
    </div>

    <nav class="nav-right">
        <a href="leaderboard.php" class="nav-btn <?= $current === 'leaderboard.php' ? 'active' : '' ?>">Leaderboard</a>
        <a href="menu.php" class="nav-btn <?= $current === 'menu.php' ? 'active' : '' ?>">Jeu</a>
        <a href="settings.php" class="nav-btn <?= $current === 'settings.php' ? 'active' : '' ?>">Options</a>
    </nav>
</header>

<style>
    /* ===== Masquer COMPLÈTEMENT le bandeau Google ===== */
    .goog-te-banner-frame,
    .goog-te-banner-frame.skiptranslate,
    iframe.goog-te-banner-frame,
    .skiptranslate iframe,
    .skiptranslate > iframe {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        width: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    body,
    body.translated-ltr,
    body.translated-rtl {
        top: 0 !important;
        position: static !important;
        min-height: 100vh !important;
    }
    html {
        margin-top: 0 !important;
    }

    .goog-logo-link,
    .goog-logo-link:link,
    .goog-logo-link:visited,
    .goog-te-gadget img,
    .goog-te-gadget > span > a,
    .goog-te-gadget > span {
        display: none !important;
    }
    .goog-te-gadget {
        color: transparent !important;
        font-size: 0 !important;
        height: auto !important;
        line-height: 0 !important;
    }

    .goog-text-highlight {
        background-color: transparent !important;
        box-shadow: none !important;
        background: none !important;
    }
    .goog-tooltip,
    .goog-tooltip:hover {
        display: none !important;
    }

    #google_translate_element {
        display: inline-flex !important;
        align-items: center;
        min-width: 130px;
        height: auto;
        line-height: normal !important;
    }

    #google_translate_element .goog-te-gadget {
        display: inline-flex !important;
        align-items: center;
        height: auto !important;
        line-height: normal !important;
        font-size: 0 !important;
    }

    #google_translate_element .goog-te-gadget > div {
        display: inline-block !important;
    }

    .goog-te-combo {
        background: linear-gradient(180deg, #4a2c12 0%, #2a1808 100%) !important;
        color: var(--gold) !important;
        border: 2px solid var(--gold) !important;
        padding: 8px 30px 8px 14px !important;
        border-radius: 4px !important;
        font-family: 'Cinzel', serif !important;
        font-size: 13px !important;
        font-weight: bold !important;
        letter-spacing: 1.5px !important;
        text-transform: uppercase !important;
        cursor: pointer;
        outline: none;
        min-width: 120px !important;
        height: auto !important;
        line-height: 1.4 !important;
        box-shadow:
            0 4px 0 #1a0f06,
            inset 0 0 10px rgba(0,0,0,0.5);
        transition: all 0.25s ease;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'><path fill='%23d4af37' d='M0 0l5 6 5-6z'/></svg>") !important;
        background-repeat: no-repeat !important;
        background-position: right 10px center !important;
        background-size: 10px 6px !important;
    }

    .goog-te-combo:hover {
        background-color: #6b3f1c !important;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'><path fill='%23ffffff' d='M0 0l5 6 5-6z'/></svg>") !important;
        color: #fff !important;
        text-shadow: 0 0 8px var(--gold);
        box-shadow:
            0 4px 0 #1a0f06,
            0 0 15px var(--gold),
            inset 0 0 10px rgba(212, 175, 55, 0.3);
        transform: translateY(-2px);
    }

    .goog-te-combo option {
        background: #2a1808 !important;
        color: var(--gold) !important;
        font-family: 'Cinzel', serif !important;
        font-weight: bold;
        padding: 5px !important;
    }

    /* ===== Indicateur de chargement pendant l'init du widget ===== */
    #gt-loading-placeholder {
        display: inline-flex;
        align-items: center;
        min-width: 120px;
        height: 38px;
        background: linear-gradient(180deg, #4a2c12 0%, #2a1808 100%);
        border: 2px solid var(--gold);
        border-radius: 4px;
        justify-content: center;
        color: var(--gold);
        font-family: 'Cinzel', serif;
        font-size: 11px;
        letter-spacing: 1px;
        opacity: 0.7;
        animation: gt-pulse 1.2s ease-in-out infinite;
    }

    @keyframes gt-pulse {
        0%, 100% { opacity: 0.4; }
        50%       { opacity: 0.9; }
    }
</style>

<script type="text/javascript">
(function() {

    /* ============================================================
       1. CONFIGURATION CENTRALE
    ============================================================ */
    var GT_CONFIG = {
        pageLanguage    : 'fr',
        includedLanguages: 'fr,en,es,de,it,pt,ru,ja,zh-CN,ar,ko',
        containerId     : 'google_translate_element',
        maxRetries      : 10,      // tentatives de réinitialisation max
        retryDelay      : 800,     // ms entre chaque tentative
        watchInterval   : 2000     // ms pour la surveillance continue
    };

    var _retryCount    = 0;
    var _watchTimer    = null;
    var _initialized   = false;

    /* ============================================================
       2. FONCTION D'INITIALISATION (appelée par Google ET en secours)
    ============================================================ */
    window.googleTranslateElementInit = function() {
        var container = document.getElementById(GT_CONFIG.containerId);
        if (!container) return;

        /* Si le combo est déjà là, inutile de réinitialiser */
        if (container.querySelector('.goog-te-combo')) {
            _initialized = true;
            _removePlaceholder();
            return;
        }

        try {
            new google.translate.TranslateElement({
                pageLanguage     : GT_CONFIG.pageLanguage,
                includedLanguages: GT_CONFIG.includedLanguages,
                layout           : google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay      : false
            }, GT_CONFIG.containerId);

            _initialized = true;
            _removePlaceholder();

            /* Restaurer la langue sauvegardée si l'utilisateur en a choisi une */
            _restoreSavedLanguage();

        } catch(e) {
            console.warn('[GT] Erreur init:', e);
            _scheduleRetry();
        }
    };

    /* ============================================================
       3. PLACEHOLDER VISUEL (évite un "trou" pendant le chargement)
    ============================================================ */
    function _showPlaceholder() {
        var container = document.getElementById(GT_CONFIG.containerId);
        if (!container || container.querySelector('.goog-te-combo')) return;
        if (document.getElementById('gt-loading-placeholder')) return;

        var ph = document.createElement('div');
        ph.id          = 'gt-loading-placeholder';
        ph.textContent = '🌐 Langue…';
        container.appendChild(ph);
    }

    function _removePlaceholder() {
        var ph = document.getElementById('gt-loading-placeholder');
        if (ph) ph.remove();
    }

    /* ============================================================
       4. SAUVEGARDE / RESTAURATION DE LA LANGUE CHOISIE
       (persiste entre les changements de page)
    ============================================================ */
    function _saveLanguageChoice(lang) {
        try { localStorage.setItem('gt_lang', lang); } catch(e) {}
    }

    function _restoreSavedLanguage() {
        var savedLang = null;
        try { savedLang = localStorage.getItem('gt_lang'); } catch(e) {}

        /* On lit aussi le cookie que Google pose lui-même */
        var cookieLang = _getGoogleCookieLang();

        var lang = savedLang || cookieLang;
        if (!lang || lang === GT_CONFIG.pageLanguage) return;

        /* Attendre que le combo soit disponible puis sélectionner */
        _waitForCombo(function(combo) {
            if (combo.value !== lang) {
                combo.value = lang;
                combo.dispatchEvent(new Event('change'));
            }
        });
    }

    function _getGoogleCookieLang() {
        var match = document.cookie.match(/googtrans=\/[a-z-]+\/([a-z-]+)/i);
        return match ? match[1] : null;
    }

    /* ============================================================
       5. ÉCOUTE DU CHANGEMENT DE LANGUE POUR LE SAUVEGARDER
    ============================================================ */
    function _bindComboChange(combo) {
        if (combo.dataset.gtBound) return;
        combo.dataset.gtBound = '1';
        combo.addEventListener('change', function() {
            _saveLanguageChoice(this.value);
        });
    }

    /* ============================================================
       6. ATTENTE DU COMBO (utilitaire)
    ============================================================ */
    function _waitForCombo(callback, tries) {
        tries = tries || 0;
        var combo = document.querySelector('#' + GT_CONFIG.containerId + ' .goog-te-combo');
        if (combo) {
            callback(combo);
        } else if (tries < 20) {
            setTimeout(function() { _waitForCombo(callback, tries + 1); }, 300);
        }
    }

    /* ============================================================
       7. RÉINITIALISATION EN CAS D'ÉCHEC
    ============================================================ */
    function _scheduleRetry() {
        if (_retryCount >= GT_CONFIG.maxRetries) {
            console.warn('[GT] Abandon après ' + GT_CONFIG.maxRetries + ' tentatives.');
            return;
        }
        _retryCount++;
        setTimeout(function() {
            if (typeof google !== 'undefined' && google.translate) {
                window.googleTranslateElementInit();
            } else {
                _injectScript(); /* recharger le script si Google n'est pas encore dispo */
            }
        }, GT_CONFIG.retryDelay * _retryCount); /* délai croissant */
    }

    /* ============================================================
       8. INJECTION DYNAMIQUE DU SCRIPT GOOGLE (secours)
    ============================================================ */
    function _injectScript() {
        if (document.getElementById('gt-script')) return;
        var s = document.createElement('script');
        s.id  = 'gt-script';
        s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        s.async = true;
        s.onerror = function() {
            console.warn('[GT] Impossible de charger le script Google Translate.');
        };
        document.head.appendChild(s);
    }

    /* ============================================================
       9. SURVEILLANCE CONTINUE : recrée le widget s'il disparaît
    ============================================================ */
    function _startWatcher() {
        if (_watchTimer) clearInterval(_watchTimer);

        _watchTimer = setInterval(function() {
            var container = document.getElementById(GT_CONFIG.containerId);
            if (!container) return;

            var combo = container.querySelector('.goog-te-combo');

            if (!combo) {
                /* Widget disparu → on réinitialise */
                _initialized = false;
                _retryCount  = 0;
                _showPlaceholder();

                if (typeof google !== 'undefined' && google.translate) {
                    window.googleTranslateElementInit();
                } else {
                    _injectScript();
                }
            } else {
                /* Widget présent → on s'assure qu'on écoute les changements */
                _bindComboChange(combo);
                _removePlaceholder();

                /* Corriger le body si Google l'a décalé */
                if (document.body.style.top && document.body.style.top !== '0px') {
                    document.body.style.top = '0px';
                }
            }
        }, GT_CONFIG.watchInterval);
    }

    /* ============================================================
       10. SUPPRESSION DU BANDEAU GOOGLE (robuste)
    ============================================================ */
    function _killBanner() {
        var b = document.querySelector('.goog-te-banner-frame');
        if (b) {
            b.style.cssText = 'display:none!important;height:0!important;';
        }
        if (document.body) {
            document.body.style.top      = '0px';
            document.body.style.position = 'static';
        }
    }

    /* ============================================================
       11. DÉMARRAGE
    ============================================================ */
    document.addEventListener('DOMContentLoaded', function() {

        /* Afficher le placeholder immédiatement */
        _showPlaceholder();

        /* Lancer la surveillance */
        _startWatcher();

        /* Observer les mutations pour catcher le bandeau */
        var obs = new MutationObserver(_killBanner);
        obs.observe(document.body, { childList: true, subtree: true });

        /* Sécurité intervalle pour le bandeau */
        setInterval(_killBanner, 500);

        /* Si Google Translate est déjà chargé (cache navigateur), init directe */
        if (typeof google !== 'undefined' && google.translate) {
            window.googleTranslateElementInit();
        }
    });

})();
</script>

<!-- Script Google Translate (chargé en async pour ne pas bloquer la page) -->
<script id="gt-script"
        async
        src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
</script>

<script src="../js/skin-secret.js" defer></script>
