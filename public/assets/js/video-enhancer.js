// video-enhancer.js
// JS za Smart Video Embed plugin

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sve-generator-form');
    if (!form) return;

    // Inputi
    const urlInput = document.getElementById('sve_url');
    const titleInput = document.getElementById('sve_title');
    // Thumbnail
    const thumbUrlRadio = document.getElementById('thumb_url_radio');
    const thumbUploadRadio = document.getElementById('thumb_upload_radio');
    const thumbUrlInput = document.getElementById('sve_thumbnail_url');
    const thumbUploadInput = document.getElementById('sve_thumbnail_upload');
    // Lottie
    const lottieUrlRadio = document.getElementById('lottie_url_radio');
    const lottieUploadRadio = document.getElementById('lottie_upload_radio');
    const lottieUrlInput = document.getElementById('sve_lottie_url');
    const lottieUploadInput = document.getElementById('sve_lottie_upload');

    const previewBtn = document.getElementById('sve-preview-btn');
    const generateBtn = document.getElementById('sve-generate-btn');
    const previewDiv = document.getElementById('sve-preview');
    const shortcodeDiv = document.getElementById('sve-shortcode-result');

    // Prikaz/skrivanje inputa za thumbnail
    function updateThumbInput() {
        if (thumbUrlRadio && thumbUrlRadio.checked) {
            thumbUrlInput.classList.remove('d-none');
            thumbUploadInput.classList.add('d-none');
        } else if (thumbUploadRadio && thumbUploadRadio.checked) {
            thumbUrlInput.classList.add('d-none');
            thumbUploadInput.classList.remove('d-none');
        }
    }
    if (thumbUrlRadio) thumbUrlRadio.addEventListener('change', updateThumbInput);
    if (thumbUploadRadio) thumbUploadRadio.addEventListener('change', updateThumbInput);
    updateThumbInput();

    // Prikaz/skrivanje inputa za lottie
    function updateLottieInput() {
        if (lottieUrlRadio && lottieUrlRadio.checked) {
            lottieUrlInput.classList.remove('d-none');
            lottieUploadInput.classList.add('d-none');
        } else if (lottieUploadRadio && lottieUploadRadio.checked) {
            lottieUrlInput.classList.add('d-none');
            lottieUploadInput.classList.remove('d-none');
        }
    }
    if (lottieUrlRadio) lottieUrlRadio.addEventListener('change', updateLottieInput);
    if (lottieUploadRadio) lottieUploadRadio.addEventListener('change', updateLottieInput);
    updateLottieInput();

    // Shortcode generisanje
    function generateShortcode(url, thumbnail, lottie) {
        let shortcode = '[smart_video';
        if (url) shortcode += ' url="' + url + '"';
        if (thumbnail) shortcode += ' thumbnail="' + thumbnail + '"';
        if (lottie) shortcode += ' lottie="' + lottie + '"';
        
        // Dodaj aspect_ratio
        const aspectRatio = document.querySelector('input[name="sve_aspect_ratio"]:checked');
        if (aspectRatio) {
            shortcode += ' aspect_ratio="' + aspectRatio.value + '"';
        }
        // Dodaj max_width
        const maxWidth = document.getElementById('sve_max_width');
        if (maxWidth && maxWidth.value) {
            shortcode += ' max_width="' + maxWidth.value + '"';
        }
        
        // Dodaj show_controls
        const showControls = document.getElementById('sve_show_controls');
        if (showControls && showControls.checked) {
            shortcode += ' show_controls="1"';
        }
        // Dodaj disable_related
        const disableRelated = document.getElementById('sve_disable_related');
        if (disableRelated && disableRelated.checked) {
            shortcode += ' disable_related="1"';
        }
        
        // Dodaj modal parametar
        const modalCheckbox = document.getElementById('sve_modal');
        if (modalCheckbox && modalCheckbox.checked) {
            shortcode += ' modal="1"';
        }
        
        // Dodaj skip parametar
        const skipCheckbox = document.getElementById('sve_lottie_skip');
        if (skipCheckbox && skipCheckbox.checked) {
            shortcode += ' skip_button="1"';
        }
        
        // Dodaj autoplay parametar
        const autoplayCheckbox = document.getElementById('sve_autoplay');
        if (autoplayCheckbox && autoplayCheckbox.checked) {
            shortcode += ' autoplay="1"';
        }
        
        // Dodaj mute parametar
        const muteCheckbox = document.getElementById('sve_mute');
        if (muteCheckbox && muteCheckbox.checked) {
            shortcode += ' mute="1"';
        }
        
        shortcode += ']';
        return shortcode;
    }

    // Učitavanje lottie-web ako treba
    function loadLottieScript(cb) {
        if (window.lottie) return cb();
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js';
        script.onload = cb;
        document.body.appendChild(script);
    }
    
    // Učitaj lottie-web na frontendu ako nije već učitano
    if (typeof lottie === 'undefined') {
        loadLottieScript(function() {
            console.log('Lottie loaded on frontend');
        });
    }

    // Prikaz preview-a sa Lottie animacijom
    function showPreviewWithLottie(url, thumbnail, lottieUrl, showSkip) {
        // Prvo prikaži video/thumbnail
        let videoHtml = '';
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            let videoId = '';
            if (url.includes('youtu.be/')) {
                videoId = url.split('youtu.be/')[1].split(/[?&]/)[0];
            } else {
                const match = url.match(/[?&]v=([^&]+)/);
                if (match) videoId = match[1];
            }
            if (videoId) {
                videoHtml = `<div style="max-width:560px;position:relative;"><iframe width="100%" height="315" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe>`;
            }
        }
        if (!videoHtml && thumbnail) {
            videoHtml = `<div style="max-width:560px;position:relative;"><img src="${thumbnail}" alt="Video thumbnail" style="width:100%;border-radius:8px;box-shadow:0 2px 8px #0002;"></div>`;
        } else if (!videoHtml) {
            videoHtml = '<div style="max-width:560px;position:relative;height:315px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;"><div>Video placeholder</div></div>';
        }
        
        // Dodaj overlay za Lottie animaciju
        const overlayHtml = `
            <div id="sve-lottie-overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:10;">
                <div id="sve-lottie-preview" style="width:200px;height:200px;"></div>
                ${showSkip ? '<button id="sve-lottie-skip" class="btn btn-warning" style="position:absolute;top:10px;right:10px;">Skip</button>' : ''}
            </div>
        `;
        
        console.log('Overlay HTML:', overlayHtml);
        console.log('showSkip value:', showSkip);
        
        // Kombinuj video i overlay
        const containerHtml = videoHtml + overlayHtml;
        console.log('Final container HTML:', containerHtml);
        previewDiv.innerHTML = containerHtml;
        
        loadLottieScript(function() {
            console.log('Loading Lottie from:', lottieUrl);
            fetch(lottieUrl)
                .then(r => {
                    console.log('Fetch response status:', r.status);
                    if (!r.ok) {
                        throw new Error(`HTTP ${r.status}: ${r.statusText}`);
                    }
                    return r.json();
                })
                .then(animData => {
                    console.log('Lottie data loaded:', animData);
                    const anim = window.lottie.loadAnimation({
                        container: document.getElementById('sve-lottie-preview'),
                        renderer: 'svg',
                        loop: false,
                        autoplay: true,
                        animationData: animData
                    });
                    anim.addEventListener('complete', function() {
                        // Sakrij overlay kada animacija završi
                        const overlay = document.getElementById('sve-lottie-overlay');
                        if (overlay) {
                            overlay.style.display = 'none';
                        }
                    });
                    if (showSkip) {
                        const skipButton = document.getElementById('sve-lottie-skip');
                        console.log('Skip button found:', skipButton);
                        if (skipButton) {
                            skipButton.onclick = function() {
                                anim.destroy();
                                const overlay = document.getElementById('sve-lottie-overlay');
                                if (overlay) {
                                    overlay.style.display = 'none';
                                }
                            };
                        } else {
                            console.error('Skip button not found in DOM');
                        }
                    }
                })
                .catch((error) => {
                    console.error('Lottie error:', error);
                    previewDiv.innerHTML = '<div class="alert alert-danger">Greška pri učitavanju Lottie animacije: ' + error.message + '</div>';
                });
        });
    }

    // Izmenjena showPreview funkcija
    function showPreview(url, thumbnail, forceVideo) {
        // Ako postoji lottie i nije forceVideo, prikazujemo prvo animaciju
        let lottieUrl = '';
        let showSkip = false;
        if (!forceVideo) {
            if (lottieUrlRadio && lottieUrlRadio.checked) {
                lottieUrl = lottieUrlInput.value.trim();
            } else if (lottieUploadInput && lottieUploadInput.files.length > 0 && lottieUploadInput.dataset.uploaded) {
                lottieUrl = lottieUploadInput.dataset.uploaded;
            }
            showSkip = document.getElementById('sve_lottie_skip')?.checked;
        }
        
        console.log('showPreview called with:', { url, thumbnail, forceVideo, lottieUrl, showSkip });
        
        if (lottieUrl && !forceVideo) {
            console.log('Calling showPreviewWithLottie');
            showPreviewWithLottie(url, thumbnail, lottieUrl, showSkip);
            return;
        }
        
        console.log('Calling regular showPreview (no Lottie)');
        // ... stari kod za prikaz videa ...
        if (!url) {
            previewDiv.innerHTML = '<div class="alert alert-warning">Unesite video URL za preview.</div>';
            return;
        }
        let embedHtml = '';
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            let videoId = '';
            if (url.includes('youtu.be/')) {
                videoId = url.split('youtu.be/')[1].split(/[?&]/)[0];
            } else {
                const match = url.match(/[?&]v=([^&]+)/);
                if (match) videoId = match[1];
            }
            if (videoId) {
                embedHtml = `<div style="max-width:560px;"><iframe width="100%" height="315" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe></div>`;
            }
        }
        if (!embedHtml && thumbnail) {
            embedHtml = `<img src="${thumbnail}" alt="Video thumbnail" style="max-width:100%;border-radius:8px;box-shadow:0 2px 8px #0002;">`;
        } else if (!embedHtml) {
            embedHtml = '<div class="alert alert-info">Nije podržan preview za ovaj URL. Proverite thumbnail.</div>';
        }
        previewDiv.innerHTML = embedHtml;
    }

    // Helper za upload
    function uploadFile(file, type, cb) {
        const formData = new FormData();
        formData.append('action', 'sve_upload_file');
        formData.append('_wpnonce', sveUpload.nonce);
        formData.append('file', file);
        formData.append('type', type);
        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                cb(null, data.data.url);
            } else {
                cb(data.data.message || 'Greška pri uploadu');
            }
        })
        .catch(() => cb('Greška pri uploadu'));
    }

    if (thumbUploadInput) thumbUploadInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            uploadFile(this.files[0], 'thumbnail', function(err, url) {
                if (err) {
                    alert('Thumbnail upload greška: ' + err);
                    thumbUploadInput.value = '';
                } else {
                    thumbUploadInput.dataset.uploaded = url;
                    alert('Thumbnail uspešno uploadovan!');
                }
            });
        }
    });
    if (lottieUploadInput) lottieUploadInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            uploadFile(this.files[0], 'lottie', function(err, url) {
                if (err) {
                    alert('Lottie upload greška: ' + err);
                    lottieUploadInput.value = '';
                } else {
                    lottieUploadInput.dataset.uploaded = url;
                    alert('Lottie JSON uspešno uploadovan!');
                }
            });
        }
    });

    if (previewBtn) previewBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let thumbnail = '';
        if (thumbUrlRadio && thumbUrlRadio.checked) {
            thumbnail = thumbUrlInput.value.trim();
        } else if (thumbUploadInput && thumbUploadInput.files.length > 0 && thumbUploadInput.dataset.uploaded) {
            thumbnail = thumbUploadInput.dataset.uploaded;
        }
        showPreview(urlInput.value.trim(), thumbnail, false);
    });

    if (generateBtn) generateBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let thumbnail = '';
        let lottie = '';
        if (thumbUrlRadio && thumbUrlRadio.checked) {
            thumbnail = thumbUrlInput.value.trim();
        } else if (thumbUploadInput && thumbUploadInput.files.length > 0 && thumbUploadInput.dataset.uploaded) {
            thumbnail = thumbUploadInput.dataset.uploaded;
        }
        if (lottieUrlRadio && lottieUrlRadio.checked) {
            lottie = lottieUrlInput.value.trim();
        } else if (lottieUploadInput && lottieUploadInput.files.length > 0 && lottieUploadInput.dataset.uploaded) {
            lottie = lottieUploadInput.dataset.uploaded;
        }
        const shortcode = generateShortcode(urlInput.value.trim(), thumbnail, lottie);
        document.getElementById('sve-shortcode-text').textContent = shortcode;
        shortcodeDiv.classList.remove('d-none');
        if (window.getSelection && document.createRange) {
            const range = document.createRange();
            range.selectNodeContents(document.getElementById('sve-shortcode-text'));
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }

        // --- AJAX upis u bazu ---
        const params = {
            url: urlInput.value.trim(),
            thumbnail: thumbnail,
            lottie: lottie,
            skip_button: document.getElementById('sve_lottie_skip')?.checked ? '1' : '0',
            lazy_load: document.getElementById('sve_lazy_load')?.checked ? '1' : '0',
            modal: document.getElementById('sve_modal')?.checked ? '1' : '0',
            autoplay: document.getElementById('sve_autoplay')?.checked ? '1' : '0',
            mute: document.getElementById('sve_mute')?.checked ? '1' : '0',
            aspect_ratio: document.querySelector('input[name="sve_aspect_ratio"]:checked')?.value || '16:9',
            max_width: document.getElementById('sve_max_width')?.value || '800px',
            show_controls: document.getElementById('sve_show_controls')?.checked ? '1' : '0',
            disable_related: document.getElementById('sve_disable_related')?.checked ? '1' : '0'
        };
        console.log('SVE params for AJAX:', params);
        const data = new FormData();
        const titleInput = document.getElementById('sve_title');
        data.append('action', 'sve_save_video');
        data.append('nonce', sveUpload.nonce);
        data.append('title', titleInput ? titleInput.value.trim() : '');
        data.append('shortcode', shortcode);
        data.append('params', JSON.stringify(params));
        fetch(sveUpload.ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.success && resp.data && resp.data.id) {
                // Prikaz i kopiranje: uvek samo [smart_video_embed id="X"]
                let newShortcode = '[smart_video_embed id="' + resp.data.id + '"]';
                document.getElementById('sve-shortcode-text').textContent = newShortcode;
            }
        });
    });
}); 

// === FRONTEND MODAL LOGIC ===
document.addEventListener('DOMContentLoaded', function() {
    // For every .sve-video-container on the page
    document.querySelectorAll('.sve-video-container').forEach(function(container) {
        const isModal = container.getAttribute('data-modal') === '1';
        const hasLottie = container.getAttribute('data-lottie') === '1';
        const lottieOverlay = container.querySelector('.sve-lottie-overlay');
        const thumb = container.querySelector('.sve-video-thumbnail');
        const video = container.querySelector('.sve-video-iframe');
        
        console.log('Container initialized:', {
            isModal: isModal,
            hasLottie: hasLottie,
            hasLottieOverlay: !!lottieOverlay,
            hasThumb: !!thumb,
            hasVideo: !!video
        });
        
        // MODAL MODE: Prikaži thumbnail, sakrij video dok se ne otvori modal
        if (isModal) {
            if (thumb && thumb.style.display === 'block') {
                if (video) video.style.display = 'none';
            } else {
                if (video) video.style.display = 'block';
                if (thumb) thumb.style.display = 'none';
            }
        } else {
            // Regular mode: video je uvek prikazan, thumbnail sakriven
            if (video) video.style.display = 'block';
            if (thumb) thumb.style.display = 'none';
        }
        if (lottieOverlay) {
            lottieOverlay.style.display = 'flex';
            console.log('Lottie overlay shown on video');
        }
        
        // Lottie skip button logic
        if (lottieOverlay) {
            const skipBtn = lottieOverlay.querySelector('.sve-lottie-skip-btn');
            if (skipBtn) {
                skipBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Skip button clicked');
                    lottieOverlay.style.display = 'none';
                    if (isModal && thumb) {
                        thumb.style.display = 'block';
                        console.log('Showing thumbnail after skip');
                    }
                });
            }
        }
    });
    
    // Delegate click for all modal-enabled thumbnails
    document.body.addEventListener('click', function(e) {
        const thumb = e.target.closest('.sve-video-thumbnail');
        if (!thumb) return;
        
        // Find parent .sve-video-container
        const container = thumb.closest('.sve-video-container');
        if (!container) return;
        
        // Check if this video has modal=1 (by presence of .sve-modal-frontend sibling)
        const modal = container.parentNode.querySelector('.sve-modal-frontend');
        if (!modal) return;
        
        e.preventDefault();
        console.log('Thumbnail clicked, opening modal');
        
        // Show modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus close button for accessibility
        const closeBtn = modal.querySelector('.sve-modal-close-frontend');
        if (closeBtn) closeBtn.focus();
    });
    
    // Close modal on X click or click outside
    document.body.addEventListener('click', function(e) {
        const modal = e.target.closest('.sve-modal-frontend');
        if (!modal) return;
        
        // X dugme
        if (e.target.classList.contains('sve-modal-close-frontend')) {
            closeModal(modal);
        }
        // Klik van modal-content
        if (e.target === modal) {
            closeModal(modal);
        }
    });
    
    // ESC zatvaranje
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.sve-modal-frontend.active').forEach(function(modal) {
                closeModal(modal);
            });
        }
    });
    
    function closeModal(modal) {
        console.log('Closing modal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        // Modal se zatvara, thumbnail ostaje vidljiv
    }
}); 

// Lottie skip button fallback for all overlays
if (typeof window !== 'undefined') {
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('sve-lottie-skip-btn')) {
      var overlay = e.target.closest('.sve-lottie-overlay');
      if (overlay) {
        overlay.style.display = 'none';
        // Show thumbnail if modal mode
        var container = overlay.closest('.sve-video-container');
        if (container) {
          var isModal = container.getAttribute('data-modal') === '1';
          var thumb = container.querySelector('.sve-video-thumbnail');
          if (isModal && thumb) {
            thumb.style.display = 'block';
          }
        }
      }
    }
  });
} 

// Restore copyShortcode functionality
window.copyShortcode = function() {
    const shortcodeText = document.getElementById('sve-shortcode-text').textContent;
    navigator.clipboard.writeText(shortcodeText).then(function() {
        // Show a non-intrusive message below the shortcode box
        let msg = document.getElementById('sve-copy-msg');
        if (!msg) {
            msg = document.createElement('div');
            msg.id = 'sve-copy-msg';
            msg.className = 'text-success mt-2';
            document.getElementById('sve-shortcode-result').appendChild(msg);
        }
        msg.textContent = 'Shortcode copied!';
        setTimeout(() => { msg.textContent = ''; }, 2000);
    });
}; 