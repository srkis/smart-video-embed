// videojs-admin.js
// Custom JS for Smart Video.js Embed admin page

document.addEventListener('DOMContentLoaded', function() {
    // Custom checkbox UX (isti kao na settings strani)
    document.querySelectorAll('.sve-custom-checkbox input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const checkmark = this.nextElementSibling;
            if (this.checked) {
                checkmark.classList.add('checked');
            } else {
                checkmark.classList.remove('checked');
            }
        });
    });

    // Preview button
    var previewBtn = document.getElementById('sve-videojs-preview-btn');
    var playerInstance = null;
    var lastTheme = null;
    // ensureThemeLinks više nije potreban
    // setActiveTheme ostaje isti
    // U previewBtn click i themeSelect change handleru više ne dodajemo/brisemo <link>, samo menjamo disabled

    function setActiveTheme(theme) {
        const themeIds = ['city', 'fantasy', 'forest', 'sea', 'classic'];
    
        // Disable sve ostale <link> elemente
        themeIds.forEach(id => {
            const link = document.getElementById('vjs-theme-' + id);
            if (link) link.disabled = (id !== theme);
        });
    
        // Ako nema aktivne instance, probaj da je pronađeš po ID-u
        if (!playerInstance) {
            try {
                playerInstance = videojs.getPlayer('videojs-preview-player');
            } catch (e) {
                console.warn('Player instance not found yet.');
                return;
            }
        }
    
        // Promeni klasu na wrapperu playera
        if (playerInstance && playerInstance.el()) {
            const el = playerInstance.el();
            themeIds.forEach(id => {
                el.classList.remove('vjs-theme-' + id);
            });
            if (theme) {
                el.classList.add('vjs-theme-' + theme);
            }
        }
    }
    
    

    // Tema select change handler
    var themeSelect = document.getElementById('sve_videojs_theme');
    if (themeSelect) {
        themeSelect.addEventListener('change', function() {
            // Ako je plejer već prikazan, odmah postavi temu i bez preview klika
            if (document.getElementById('videojs-preview-player')) {
                setActiveTheme(themeSelect.value);
            }
          //  setActiveTheme(themeSelect.value);
        });
    }

    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const title = document.getElementById('sve_videojs_title').value.trim();
            const url = document.getElementById('sve_videojs_url').value.trim();
            const thumbnail = document.getElementById('sve_videojs_thumbnail').value.trim();
            const lottie = document.getElementById('sve_videojs_lottie').value.trim();
            const theme = document.getElementById('sve_videojs_theme').value;
            const skip = document.getElementById('sve_videojs_skip').checked;
            const lazy = document.getElementById('sve_videojs_lazy').checked;
            const modal = document.getElementById('sve_videojs_modal').checked;
            const autoplay = document.getElementById('sve_videojs_autoplay').checked;
            const mute = document.getElementById('sve_videojs_mute').checked;
            const hideControls = document.getElementById('sve_videojs_hide_controls')?.checked ? 1 : 0;
            const disableRelated = document.getElementById('sve_videojs_disable_related')?.checked ? 1 : 0;
            const preview = document.getElementById('sve-videojs-preview');

            // Hard reset: potpuno brišem preview sadržaj pre učitavanja CSS-a
            preview.innerHTML = '';

            function renderVideojsPreview() {
                if (!url) {
                    preview.innerHTML = '<span class="dashicons dashicons-format-video preview-icon"></span><div class="preview-text">Click \"Preview\" to see how your Video.js player will look</div>';
                    return;
                }
                // Parent div za preview
                let embedHtml = '';
                let parentStyle = 'width:560px;max-width:100%;margin:0 auto;position:relative;';
                if (url.match(/(youtube\.com|youtu\.be)/i)) {
                    embedHtml = `<div style="${parentStyle}">
                        <video id="videojs-preview-player" class="video-js vjs-big-play-centered vjs-theme-${theme}" controls preload="auto" style="width:100%;aspect-ratio:16/9;" poster="${thumbnail}">
                            <source src="${url}" type="video/youtube">
                        </video>
                    </div>`;
                } else if (url.match(/vimeo\.com/i)) {
                    embedHtml = `<div style="${parentStyle}">
                        <video id="videojs-preview-player" class="video-js vjs-big-play-centered vjs-theme-${theme}" controls preload="auto" style="width:100%;aspect-ratio:16/9;" poster="${thumbnail}">
                            <source src="${url}" type="video/vimeo">
                        </video>
                    </div>`;
                } else {
                    embedHtml = `<div style="${parentStyle}height:315px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;">Invalid or unsupported video URL</div>`;
                }
                // Lottie overlay (demo)
                if (lottie) {
                    embedHtml += `<div id="sve-videojs-lottie-overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:10;">
                        <div id="sve-videojs-lottie-preview" style="width:200px;height:200px;"></div>
                        ${skip ? '<button id="sve-videojs-lottie-skip" class="sve-videojs-lottie-skip">Skip</button>' : ''}
                    </div>`;
                }
                preview.innerHTML = embedHtml;
                // Lottie JS
                if (lottie && window.lottie) {
                    fetch(lottie)
                        .then(r => r.json())
                        .then(animData => {
                            const anim = window.lottie.loadAnimation({
                                container: document.getElementById('sve-videojs-lottie-preview'),
                                renderer: 'svg',
                                loop: false,
                                autoplay: true,
                                animationData: animData
                            });
                            anim.addEventListener('complete', function() {
                                const overlay = document.getElementById('sve-videojs-lottie-overlay');
                                if (overlay) overlay.style.display = 'none';
                            });
                            if (skip) {
                                const skipBtn = document.getElementById('sve-videojs-lottie-skip');
                                if (skipBtn) {
                                    skipBtn.onclick = function() {
                                        anim.destroy();
                                        const overlay = document.getElementById('sve-videojs-lottie-overlay');
                                        if (overlay) overlay.style.display = 'none';
                                    };
                                }
                            }
                        });
                }
                // Inicijalizacija Video.js plejera samo jednom
                setTimeout(function() {
                    var player = document.getElementById('videojs-preview-player');
                    if (player && window.videojs) {
                        var techOrder = ['youtube', 'vimeo', 'html5'];
                        if (!window.videojs.getTech('Vimeo')) {
                            techOrder = ['youtube', 'html5'];
                        }
                        playerInstance = window.videojs(player, {
                            techOrder: techOrder,
                            controls: true,
                            preload: 'auto',
                            width: 560,
                            height: 315
                        });
                        playerInstance.ready(function() {
                            playerInstance.poster(thumbnail);
                            setActiveTheme(theme); // <- odmah postavi temu
                        });
                    }
                }, 200);
            }
            renderVideojsPreview();
        });
    }

    // Generate Shortcode button
    var generateBtn = document.getElementById('sve-videojs-generate-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const title = document.getElementById('sve_videojs_title').value.trim();
            const url = document.getElementById('sve_videojs_url').value.trim();
            const thumbnail = document.getElementById('sve_videojs_thumbnail').value.trim();
            const lottie = document.getElementById('sve_videojs_lottie').value.trim();
            const theme = document.getElementById('sve_videojs_theme').value;
            const skip = document.getElementById('sve_videojs_skip').checked;
            const lazy = document.getElementById('sve_videojs_lazy').checked;
            const modal = document.getElementById('sve_videojs_modal').checked;
            const autoplay = document.getElementById('sve_videojs_autoplay').checked;
            const mute = document.getElementById('sve_videojs_mute').checked;
            const hideControls = document.getElementById('sve_videojs_hide_controls')?.checked ? 1 : 0;
            const disableRelated = document.getElementById('sve_videojs_disable_related')?.checked ? 1 : 0;
            // Use the entered title or default to 'Video.js'
            let videoTitle = title || 'Video.js';
            
            // Generisi pun shortcode za bazu
            let fullShortcode = '[smart_video_embed type="videojs"';
            if (url) fullShortcode += ' url="' + url + '"';
            if (thumbnail) fullShortcode += ' thumbnail="' + thumbnail + '"';
            if (lottie) fullShortcode += ' lottie="' + lottie + '"';
            if (theme) fullShortcode += ' theme="' + theme + '"';
            if (skip) fullShortcode += ' skip_button="1"';
            if (lazy) fullShortcode += ' lazy_load="1"';
            if (modal) fullShortcode += ' modal="1"';
            if (autoplay) fullShortcode += ' autoplay="1"';
            if (mute) fullShortcode += ' mute="1"';
            if (hideControls) fullShortcode += ' hide_controls="1"';
            if (disableRelated) fullShortcode += ' disable_related="1"';
            fullShortcode += ']';

            // Prikaz rezultata odmah (dok ne stigne AJAX)
            var resultBox = document.getElementById('sve-videojs-shortcode-result');
            var codeBox = document.getElementById('sve-videojs-shortcode-text');
            if (resultBox && codeBox) {
                codeBox.textContent = fullShortcode; // Privremeno prikaži pun shortcode
                resultBox.classList.remove('d-none');
            }

            // AJAX upis u bazu
            if (typeof sveUpload !== 'undefined') {
                var data = {
                    action: 'sve_save_video',
                    nonce: (typeof sveUpload !== 'undefined') ? sveUpload.nonce : '',
                    title: videoTitle,
                    shortcode: fullShortcode, // Šaljemo pun shortcode u bazu
                    params: JSON.stringify({
                        url: url,
                        thumbnail: thumbnail,
                        lottie: lottie,
                        theme: theme,
                        skip_button: skip ? 1 : 0,
                        lazy_load: lazy ? 1 : 0,
                        modal: modal ? 1 : 0,
                        autoplay: autoplay ? 1 : 0,
                        mute: mute ? 1 : 0,
                        aspect_ratio: document.querySelector('input[name="sve_videojs_aspect_ratio"]:checked')?.value || '16:9',
                        max_width: document.getElementById('sve_videojs_max_width')?.value || '800px',
                        hide_controls: hideControls,
                        disable_related: disableRelated
                    })
                };
                const fetchUrl = sveUpload.ajaxurl;
                fetch(fetchUrl, {
                    method: 'POST',
                    body: new URLSearchParams(Object.entries(data))
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.success && resp.data && resp.data.id) {
                        // Prikaz ID shortcode-a korisniku
                        if (codeBox && resultBox) {
                            codeBox.textContent = '[smart_video_embed id="' + resp.data.id + '"]';
                            resultBox.classList.remove('d-none');
                            // Promeni naslov na "Recommended Shortcode"
                            var heading = resultBox.querySelector('h6');
                            if (heading) heading.innerHTML = '<i class="dashicons dashicons-shortcode me-1"></i> Recommended Shortcode (auto-updates everywhere)';
                        }
                    }
                });
            }
        });
    }

    // Copy Shortcode
    window.copyVideojsShortcode = function() {
        var codeBox = document.getElementById('sve-videojs-shortcode-text');
        if (codeBox) {
            navigator.clipboard.writeText(codeBox.textContent).then(function() {
                // Prikaz nenametljive poruke
                var btn = document.querySelector('.btn-copy-shortcode');
                if (btn) {
                    var orig = btn.innerHTML;
                    btn.innerHTML = '<i class="dashicons dashicons-yes"></i> Copied!';
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success');
                    setTimeout(function() {
                        btn.innerHTML = orig;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-primary');
                    }, 1500);
                }
            });
        }
    };
}); 