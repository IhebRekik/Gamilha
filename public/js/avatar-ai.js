/* Avatar AI - Appel direct vers /api/avatar/cartoonify (Hugging Face via PHP) */
/* Fichier ASCII pur - pas de caracteres speciaux dans le code */
(function(window) {
    'use strict';

    var API_URL = '/api/avatar/cartoonify';
    var MAX_RETRIES = 5; /* tentatives si le modele HF est en warm-up */

    /* ================================================================
     *  STYLES (injectes une seule fois, ASCII pur)
     * ================================================================ */
    function injectStyles() {
        if (document.getElementById('aai-styles')) return;
        var s = document.createElement('style');
        s.id = 'aai-styles';
        s.textContent =
            '.aai-card{background:linear-gradient(145deg,#0f0f1a,#1a1028);border:1.5px solid rgba(139,92,246,.4);border-radius:20px;overflow:hidden;margin-top:1rem;font-family:inherit;}' +
            '.aai-head{background:linear-gradient(135deg,#8b5cf6,#ec4899);padding:.6rem 1rem;display:flex;align-items:center;gap:.5rem;}' +
            '.aai-head-dot{width:8px;height:8px;border-radius:50%;background:#fff;opacity:.9;animation:aai-blink 1.6s infinite;}' +
            '@keyframes aai-blink{0%,100%{opacity:.9}50%{opacity:.2}}' +
            '.aai-head-title{font-weight:700;font-size:.82rem;color:#fff;letter-spacing:.07em;text-transform:uppercase;}' +
            '.aai-body{display:flex;gap:1rem;padding:1rem;align-items:center;flex-wrap:wrap;}' +
            '.aai-ring-wrap{position:relative;flex-shrink:0;}' +
            '.aai-ring{border-radius:50%;background:linear-gradient(135deg,#8b5cf6,#ec4899);padding:3px;display:inline-block;box-shadow:0 0 20px rgba(139,92,246,.4);}' +
            '.aai-avatar{border-radius:50%;display:block;object-fit:cover;background:#1a1028;}' +
            '.aai-badge{position:absolute;bottom:-4px;right:-4px;background:linear-gradient(135deg,#8b5cf6,#ec4899);border-radius:20px;font-size:.6rem;font-weight:700;color:#fff;padding:.15rem .4rem;}' +
            '.aai-side{flex:1;min-width:140px;}' +
            '.aai-label{font-size:.75rem;font-weight:600;color:#c084fc;margin-bottom:.3rem;}' +
            '.aai-desc{font-size:.7rem;color:rgba(200,200,220,.55);line-height:1.4;margin-bottom:.7rem;}' +
            '.aai-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1.1rem;border-radius:30px;border:none;cursor:pointer;font-weight:700;font-size:.8rem;transition:transform .15s,box-shadow .15s;font-family:inherit;}' +
            '.aai-btn-primary{background:linear-gradient(135deg,#8b5cf6,#ec4899);color:#fff;box-shadow:0 4px 14px rgba(139,92,246,.4);}' +
            '.aai-btn-primary:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 6px 20px rgba(139,92,246,.6);}' +
            '.aai-btn-primary:disabled{opacity:.45;cursor:not-allowed;}' +
            '.aai-btn-green{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 3px 10px rgba(16,185,129,.3);}' +
            '.aai-btn-green:hover{transform:translateY(-1px);}' +
            '.aai-btn-ghost{background:rgba(139,92,246,.12);color:#c084fc;border:1px solid rgba(139,92,246,.3);text-decoration:none;}' +
            '.aai-btn-ghost:hover{background:rgba(139,92,246,.22);}' +
            '.aai-load{display:flex;flex-direction:column;align-items:center;padding:1.2rem;gap:.6rem;}' +
            '.aai-spin{width:46px;height:46px;border:4px solid rgba(139,92,246,.2);border-top-color:#8b5cf6;border-radius:50%;animation:aai-rot .75s linear infinite;}' +
            '@keyframes aai-rot{to{transform:rotate(360deg)}}' +
            '.aai-load-txt{font-size:.76rem;color:#c084fc;font-weight:600;text-align:center;}' +
            '.aai-load-sub{font-size:.68rem;color:rgba(200,200,220,.45);text-align:center;}' +
            '.aai-pbar{height:3px;background:rgba(139,92,246,.1);border-radius:2px;width:200px;margin-top:.3rem;}' +
            '.aai-pfill{height:3px;background:linear-gradient(90deg,#8b5cf6,#ec4899);border-radius:2px;width:0;transition:width .5s ease;}' +
            '.aai-result{padding:.8rem 1rem;border-top:1px solid rgba(139,92,246,.15);}' +
            '.aai-compare{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;margin-bottom:.7rem;}' +
            '.aai-clabel{font-size:.6rem;color:rgba(200,200,220,.4);text-align:center;margin-top:.2rem;}' +
            '.aai-arrow{font-size:1.1rem;color:#8b5cf6;}' +
            '.aai-actions{display:flex;flex-direction:column;gap:.4rem;}' +
            '.aai-err{display:flex;align-items:flex-start;gap:.5rem;padding:.7rem 1rem;font-size:.75rem;color:#fca5a5;background:rgba(239,68,68,.07);border-top:1px solid rgba(239,68,68,.18);}' +
            '.aai-particle{position:absolute;border-radius:50%;pointer-events:none;animation:aai-fly 2s ease-out forwards;}' +
            '@keyframes aai-fly{0%{opacity:1;transform:translateY(0) scale(1)}100%{opacity:0;transform:translateY(-55px) scale(.1)}}';
        document.head.appendChild(s);
    }

    /* ================================================================
     *  UTILITAIRE
     * ================================================================ */
    function esc(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function sleep(ms) {
        return new Promise(function(r) { setTimeout(r, ms); });
    }

    function animPbar(el, from, to, dur) {
        if (!el) return;
        var t0 = null;

        function step(ts) {
            if (!t0) t0 = ts;
            var f = Math.min((ts - t0) / dur, 1);
            el.style.width = (from + (to - from) * f) + '%';
            if (f < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function particles(ringEl) {
        if (!ringEl) return;
        var colors = ['#8b5cf6', '#ec4899', '#c084fc', '#f0abfc'];
        for (var i = 0; i < 12; i++) {
            var p = document.createElement('div');
            p.className = 'aai-particle';
            var sz = 4 + Math.random() * 6;
            p.style.cssText = 'position:absolute;width:' + sz + 'px;height:' + sz + 'px;' +
                'left:' + (15 + Math.random() * 70) + '%;top:' + (15 + Math.random() * 70) + '%;' +
                'background:' + colors[i % colors.length] + ';' +
                'animation-delay:' + (Math.random() * 0.3) + 's;' +
                'animation-duration:' + (1 + Math.random() * 0.8) + 's;';
            ringEl.parentElement.style.position = 'relative';
            ringEl.parentElement.appendChild(p);
            setTimeout(function(_p) { if (_p.parentNode) _p.parentNode.removeChild(_p); }, 2200, p);
        }
    }

    /* ================================================================
     *  APPEL API — uniquement vers le controller PHP Symfony
     *  Le controller appelle Hugging Face cote serveur
     * ================================================================ */
    // Nouvelle version : envoie un prompt texte (pas l'image)
    function callApiPrompt(prompt, onProgress, attempt) {
        attempt = attempt || 1;
        var fd = new FormData();
        fd.append('prompt', prompt);

        return fetch(API_URL, { method: 'POST', body: fd })
            .then(function(res) {
                return res.text().then(function(text) {
                    var data;
                    try { data = JSON.parse(text); } catch (e) { throw new Error('Reponse invalide du serveur'); }
                    if (res.ok && data.success) {
                        return data.image; /* data URL base64 */
                    }
                    if (data.loading && attempt <= MAX_RETRIES) {
                        var wait = Math.min((data.estimated_time || 25) * 1000, 35000);
                        onProgress && onProgress(attempt, Math.ceil(wait / 1000));
                        return sleep(wait).then(function() {
                            return callApiPrompt(prompt, onProgress, attempt + 1);
                        });
                    }
                    throw new Error(data.error || ('Erreur HTTP ' + res.status));
                });
            });
    }

    /* ================================================================
     *  CONSTRUCTION DE LA CARTE UI
     * ================================================================ */
    function buildCard(inputEl, size) {
        var card = document.createElement('div');
        card.className = 'aai-card';
        card.innerHTML =
            '<div class="aai-head">' +
            '  <div class="aai-head-dot"></div>' +
            '  <span class="aai-head-title">Avatar AI &nbsp;&bull;&nbsp; Anime via Hugging Face</span>' +
            '</div>' +
            '<div class="aai-body">' +
            '  <div class="aai-ring-wrap">' +
            '    <div class="aai-ring" id="aai-ring">' +
            '      <div id="aai-orig" class="aai-avatar" style="width:' + size + 'px;height:' + size + 'px;display:flex;align-items:center;justify-content:center;">' +
            '        <i class="bi bi-camera" style="font-size:' + Math.round(size * 0.35) + 'px;color:rgba(139,92,246,.5);"></i>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="aai-side" id="aai-side">' +
            '    <div class="aai-label">Choisissez une photo</div>' +
            '    <div class="aai-desc">Selectionnez votre photo avec le bouton ci-dessus, puis cliquez sur <strong style="color:#c084fc">Transformer</strong>.</div>' +
            '    <button class="aai-btn aai-btn-primary" id="aai-btn-go" disabled>' +
            '      <i class="bi bi-stars"></i> Transformer en Anime' +
            '    </button>' +
            '  </div>' +
            '</div>';
        inputEl.parentElement.insertAdjacentElement('afterend', card);
        return card;
    }

    /* ================================================================
     *  INIT PRINCIPALE
     * ================================================================ */
    function init(inputId, previewId, opts) {
        opts = opts || {};
        var size = opts.size || 150;
        var input = document.getElementById(inputId);
        if (!input) return;

        injectStyles();

        var legacy = document.getElementById(previewId);
        if (legacy) legacy.style.display = 'none';

        var card = buildCard(input, size);
        var origEl = card.querySelector('#aai-orig');
        var btnGo = card.querySelector('#aai-btn-go');
        var currentFile = null;
        var userAge = null;
        var userSex = null;

        // Ajout du mini-formulaire après upload
        function showForm() {
            var form = document.createElement('div');
            form.style.margin = '1rem 0';
            form.innerHTML =
                '<label style="color:#c084fc;font-size:.8rem;">Age :</label> ' +
                '<select id="aai-age" style="margin:0 .7rem .7rem .5rem;">' +
                '<option value="child">Enfant</option>' +
                '<option value="teenager">Adolescent</option>' +
                '<option value="adult">Adulte</option>' +
                '<option value="senior">Senior</option>' +
                '</select>' +
                '<label style="color:#c084fc;font-size:.8rem;">Sexe :</label> ' +
                '<select id="aai-sex" style="margin:0 .7rem .7rem .5rem;">' +
                '<option value="boy">Garçon</option>' +
                '<option value="girl">Fille</option>' +
                '<option value="man">Homme</option>' +
                '<option value="woman">Femme</option>' +
                '</select>';
            card.querySelector('.aai-side').appendChild(form);
        }

        input.addEventListener('change', function(e) {
            var f = e.target.files[0];
            if (!f) return;
            if (!f.type.match(/image\/(jpeg|png|gif|webp)/)) {
                showErr(card, 'Format non supporte (JPG, PNG, WEBP)');
                input.value = '';
                return;
            }
            if (f.size > 5 * 1024 * 1024) {
                showErr(card, 'Image trop lourde (max 5 Mo)');
                input.value = '';
                return;
            }
            currentFile = f;
            clearZone(card);
            var reader = new FileReader();
            reader.onload = function(ev) {
                origEl.innerHTML = '<img src="' + ev.target.result + '" class="aai-avatar" style="width:' + size + 'px;height:' + size + 'px;object-fit:cover;">';
                var side = card.querySelector('#aai-side');
                if (side) {
                    side.querySelector('.aai-label').textContent = 'Photo prete !';
                    side.querySelector('.aai-desc').innerHTML = 'Cliquez sur <strong style="color:#c084fc">Transformer</strong> pour generer votre avatar anime IA.';
                }
                btnGo.disabled = false;
            };
            reader.readAsDataURL(f);
        });

        btnGo.addEventListener('click', function() {
            transform();
        });

        function randomPrompt() {
            var arr = [
                'anime portrait',
                'cartoon style',
                'vibrant colors',
                'studio ghibli inspired',
                'high quality',
                'detailed face',
                'soft lighting',
                'digital art',
                'professional',
                'vivid expression'
            ];
            for (var i = arr.length - 1; i > 0; i--) {
                var j = Math.floor(Math.random() * (i + 1));
                var tmp = arr[i];
                arr[i] = arr[j];
                arr[j] = tmp;
            }
            return arr.slice(0, 7 + Math.floor(Math.random() * 3)).join(', ');
        }

        function transform() {
            btnGo.disabled = true;
            clearZone(card);
            var loadEl = document.createElement('div');
            loadEl.className = 'aai-load';
            loadEl.id = 'aai-loadzone';
            loadEl.innerHTML =
                '<div class="aai-spin"></div>' +
                '<div class="aai-load-txt" id="aai-ltxt">Connexion a Hugging Face...</div>' +
                '<div class="aai-load-sub" id="aai-lsub">Generation de votre avatar IA</div>' +
                '<div class="aai-pbar"><div class="aai-pfill" id="aai-pfill"></div></div>';
            card.appendChild(loadEl);
            animPbar(loadEl.querySelector('#aai-pfill'), 0, 35, 2000);
            var prompt = randomPrompt();
            callApiPrompt(prompt, function onWarmup(attempt, eta) {
                    var ltxt = card.querySelector('#aai-ltxt');
                    var lsub = card.querySelector('#aai-lsub');
                    var pfill = card.querySelector('#aai-pfill');
                    if (ltxt) ltxt.textContent = 'Modèle en démarrage (essai ' + attempt + '/' + MAX_RETRIES + ')...';
                    if (lsub) lsub.textContent = 'Encore ~' + eta + 's, merci de patienter';
                    animPbar(pfill, parseFloat(pfill.style.width) || 35, 35 + attempt * 10, 1000);
                })
                .then(function(dataUrl) {
                    var pfill = card.querySelector('#aai-pfill');
                    animPbar(pfill, parseFloat(pfill.style.width) || 35, 100, 400);
                    setTimeout(function() {
                        clearZone(card);
                        showResult(card, null, dataUrl, size, input, opts, card.querySelector('#aai-ring'));
                        btnGo.disabled = false;
                    }, 420);
                })
                .catch(function(err) {
                    clearZone(card);
                    showErr(card, 'Echec API : ' + (err.message || 'Erreur inconnue') + '. Verifiez HUGGINGFACE_API_TOKEN dans .env');
                    btnGo.disabled = false;
                });
        }
    }

    /* ================================================================
     *  AFFICHAGE RESULTAT
     * ================================================================ */
    function showResult(card, origFile, dataUrl, size, input, opts, ringEl) {
        /* Badge sur l'anneau */
        var wrapEl = card.querySelector('.aai-ring-wrap');
        if (wrapEl) {
            var oldBadge = wrapEl.querySelector('.aai-badge');
            if (oldBadge) oldBadge.parentNode.removeChild(oldBadge);
            var badge = document.createElement('div');
            badge.className = 'aai-badge';
            badge.textContent = 'AI';
            wrapEl.appendChild(badge);
        }
        particles(ringEl);

        var s2 = Math.round(size * 0.7);
        var res = document.createElement('div');
        res.className = 'aai-result';
        // Si origFile existe, affiche la comparaison. Sinon, affiche juste l'avatar IA.
        if (origFile) {
            var origUrl = URL.createObjectURL(origFile);
            res.innerHTML =
                '<div class="aai-compare">' +
                '  <div style="text-align:center;">' +
                '    <img src="' + origUrl + '" style="width:' + s2 + 'px;height:' + s2 + 'px;border-radius:50%;object-fit:cover;border:2px solid rgba(139,92,246,.35);">' +
                '    <div class="aai-clabel">Original</div>' +
                '  </div>' +
                '  <div class="aai-arrow">&#8594;</div>' +
                '  <div style="text-align:center;">' +
                '    <img src="' + dataUrl + '" id="aai-result-img" style="width:' + s2 + 'px;height:' + s2 + 'px;border-radius:50%;object-fit:cover;border:3px solid #8b5cf6;box-shadow:0 0 18px rgba(139,92,246,.5);">' +
                '    <div class="aai-clabel" style="color:#c084fc;font-weight:700;">Anime ✨</div>' +
                '  </div>' +
                '</div>';
        } else {
            res.innerHTML =
                '<div class="aai-compare">' +
                '  <div style="text-align:center;width:100%;">' +
                '    <img src="' + dataUrl + '" id="aai-result-img" style="width:' + s2 + 'px;height:' + s2 + 'px;border-radius:50%;object-fit:cover;border:3px solid #8b5cf6;box-shadow:0 0 18px rgba(139,92,246,.5);">' +
                '    <div class="aai-clabel" style="color:#c084fc;font-weight:700;">Avatar IA ✨</div>' +
                '  </div>' +
                '</div>';
        }
        res.innerHTML +=
            '<div class="aai-actions">' +
            '  <button class="aai-btn aai-btn-green" id="aai-use-btn"><i class="bi bi-check-circle-fill"></i> Utiliser cet avatar</button>' +
            '  <a class="aai-btn aai-btn-ghost" href="' + dataUrl + '" download="avatar-anime.png"><i class="bi bi-download"></i> Telecharger</a>' +
            '  <span style="font-size:.62rem;color:rgba(200,200,220,.28);">Hugging Face &bull; Anime GAN</span>' +
            '</div>';
        card.appendChild(res);

        res.querySelector('#aai-use-btn').addEventListener('click', function() {
            applyToInput(dataUrl, input, opts);
            this.innerHTML = '<i class="bi bi-check-circle-fill"></i> Applique !';
            this.style.background = 'linear-gradient(135deg,#059669,#065f46)';
        });
    }

    /* ================================================================
     *  INJECTER L'IMAGE DANS L'INPUT FILE
     * ================================================================ */
    function applyToInput(dataUrl, input, opts) {
        var parts = dataUrl.split(',');
        var mime = (parts[0].match(/data:([^;]+);/) || [])[1] || 'image/png';
        var binary = atob(parts[1]);
        var buf = new Uint8Array(binary.length);
        for (var i = 0; i < binary.length; i++) buf[i] = binary.charCodeAt(i);
        var file = new File([new Blob([buf], { type: mime })], 'avatar-ai.png', { type: 'image/png' });
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (e) { /* DataTransfer non supporte dans ce navigateur */ }
        if (opts && opts.onDone) opts.onDone(dataUrl, file);
    }

    /* ================================================================
     *  HELPERS
     * ================================================================ */
    function clearZone(card) {
        ['#aai-loadzone', '.aai-result', '.aai-err'].forEach(function(sel) {
            var el = card.querySelector(sel);
            if (el && el.parentNode) el.parentNode.removeChild(el);
        });
    }

    function showErr(card, msg) {
        clearZone(card);
        var el = document.createElement('div');
        el.className = 'aai-err';
        el.innerHTML = '<i class="bi bi-exclamation-triangle-fill" style="color:#f87171;margin-top:.1rem;flex-shrink:0;"></i><span>' + esc(msg) + '</span>';
        card.appendChild(el);
    }

    /* ================================================================
     *  EXPORT
     * ================================================================ */
    window.AvatarAI = { init: init };

})(window);