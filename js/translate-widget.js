(function() {
    var GT_CONFIG = {
        pageLanguage    : 'fr',
        includedLanguages: 'fr,en,es,de,it,pt,ru,ja,zh-CN,ar,ko',
        containerId     : 'google_translate_element',
        maxRetries      : 10,
        retryDelay      : 800,
        watchInterval   : 2000
    };
    var _retryCount  = 0;
    var _watchTimer  = null;
    var _initialized = false;

    window.googleTranslateElementInit = function() {
        var container = document.getElementById(GT_CONFIG.containerId);
        if (!container) return;
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
            _restoreSavedLanguage();
        } catch(e) {
            console.warn('[GT] Erreur init:', e);
            _scheduleRetry();
        }
    };

    function _showPlaceholder() {
        var container = document.getElementById(GT_CONFIG.containerId);
        if (!container || container.querySelector('.goog-te-combo')) return;
        if (document.getElementById('gt-loading-placeholder')) return;
        var ph = document.createElement('div');
        ph.id = 'gt-loading-placeholder';
        ph.textContent = 'Langue…';
        container.appendChild(ph);
    }

    function _removePlaceholder() {
        var ph = document.getElementById('gt-loading-placeholder');
        if (ph) ph.remove();
    }

    function _saveLanguageChoice(lang) {
        try { localStorage.setItem('gt_lang', lang); } catch(e) {}
    }

    function _restoreSavedLanguage() {
        var savedLang = null;
        try { savedLang = localStorage.getItem('gt_lang'); } catch(e) {}
        var cookieLang = _getGoogleCookieLang();
        var lang = savedLang || cookieLang;
        if (!lang || lang === GT_CONFIG.pageLanguage) return;
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

    function _bindComboChange(combo) {
        if (combo.dataset.gtBound) return;
        combo.dataset.gtBound = '1';
        combo.addEventListener('change', function() {
            _saveLanguageChoice(this.value);
        });
    }

    function _waitForCombo(callback, tries) {
        tries = tries || 0;
        var combo = document.querySelector('#' + GT_CONFIG.containerId + ' .goog-te-combo');
        if (combo) {
            callback(combo);
        } else if (tries < 20) {
            setTimeout(function() { _waitForCombo(callback, tries + 1); }, 300);
        }
    }

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
                _injectScript();
            }
        }, GT_CONFIG.retryDelay * _retryCount);
    }

    function _injectScript() {
        if (document.getElementById('gt-script')) return;
        var s = document.createElement('script');
        s.id  = 'gt-script';
        s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        s.async = true;
        s.onerror = function() {
            console.warn('[GT] Impossible de charger Google Translate.');
        };
        document.head.appendChild(s);
    }

    function _startWatcher() {
        if (_watchTimer) clearInterval(_watchTimer);
        _watchTimer = setInterval(function() {
            var container = document.getElementById(GT_CONFIG.containerId);
            if (!container) return;
            var combo = container.querySelector('.goog-te-combo');
            if (!combo) {
                _initialized = false;
                _retryCount  = 0;
                _showPlaceholder();
                if (typeof google !== 'undefined' && google.translate) {
                    window.googleTranslateElementInit();
                } else {
                    _injectScript();
                }
            } else {
                _bindComboChange(combo);
                _removePlaceholder();
                if (document.body.style.top && document.body.style.top !== '0px') {
                    document.body.style.top = '0px';
                }
            }
        }, GT_CONFIG.watchInterval);
    }

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

    document.addEventListener('DOMContentLoaded', function() {
        _showPlaceholder();
        _startWatcher();
        var obs = new MutationObserver(_killBanner);
        obs.observe(document.body, { childList: true, subtree: true });
        setInterval(_killBanner, 500);
        if (typeof google !== 'undefined' && google.translate) {
            window.googleTranslateElementInit();
        }
    });
})();
