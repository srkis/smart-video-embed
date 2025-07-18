<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="wrap sve-admin-main" style="padding-top:32px;">
    <div class="container-fluid" style="max-width: 1400px;">
        <div class="row">
            <div class="col-12 mb-4">
                <h1 class="display-5 fw-bold mb-2" style="letter-spacing:0.01em;">Smart Video Embed</h1>
                <div class="text-muted mb-3" style="font-size:1.15rem;max-width:700px;">
                    Easily generate advanced, responsive video shortcodes with custom thumbnails, Lottie animations, lazy load, lightbox, and more. Perfect for modern WordPress sites and page builders.
                </div>
                <hr>
            </div>
        </div>
        <div class="row align-items-start">
            <!-- LEVA KOLONA: FORMA -->
            <div class="col-lg-7 col-md-12 mb-4">
                <?php
                $form_values = [
                    'title' => '',
                    'url' => '',
                    'thumbnail' => '',
                    'lottie' => '',
                    'skip_button' => false,
                    'lazy_load' => false,
                    'modal' => false,
                    'nonce_action' => 'sve_add_video',
                    'cancel_url' => admin_url('admin.php?page=sve-videos'),
                    'mode' => 'add',
                ];
                include SVE_PLUGIN_DIR . 'admin/video-form.php';
                ?>
                <!-- Shortcode Result -->
                <div id="sve-shortcode-result" class="alert alert-info d-none mt-4">
                    <h6 class="alert-heading">
                        <i class="dashicons dashicons-shortcode me-1"></i>
                        Generated Shortcode
                    </h6>
                    <div class="bg-light p-3 rounded mt-2">
                        <code id="sve-shortcode-text"></code>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-copy-shortcode" onclick="copyShortcode()">
                        <i class="dashicons dashicons-clipboard me-1"></i>
                        Copy Shortcode
                    </button>
                </div>
            </div>
            <!-- DESNA KOLONA: PREVIEW -->
            <div class="col-lg-5 col-md-12 mb-4">
                <div class="card border-secondary shadow-sm" style="min-height: 340px;">
                    <div class="card-header preview-title">
                        <h5 class="mb-0">Preview</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 260px;">
                        <div id="sve-preview" class="w-100 text-center">
                            <span class="dashicons dashicons-format-video preview-icon"></span>
                            <div class="preview-text">Click "Preview" to see how your video will look</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thumbnail type toggle
    const thumbUrlRadio = document.getElementById('thumb_url_radio');
    const thumbUploadRadio = document.getElementById('thumb_upload_radio');
    const thumbUrlInput = document.getElementById('sve_thumbnail_url');
    const thumbUploadInput = document.getElementById('sve_thumbnail_upload');

    function toggleThumbnailInputs() {
        if (thumbUrlRadio.checked) {
            thumbUrlInput.classList.remove('d-none');
            thumbUploadInput.classList.add('d-none');
        } else {
            thumbUrlInput.classList.add('d-none');
            thumbUploadInput.classList.remove('d-none');
        }
    }

    if (thumbUrlRadio) thumbUrlRadio.addEventListener('change', toggleThumbnailInputs);
    if (thumbUploadRadio) thumbUploadRadio.addEventListener('change', toggleThumbnailInputs);

    // Lottie type toggle
    const lottieUrlRadio = document.getElementById('lottie_url_radio');
    const lottieUploadRadio = document.getElementById('lottie_upload_radio');
    const lottieUrlInput = document.getElementById('sve_lottie_url');
    const lottieUploadInput = document.getElementById('sve_lottie_upload');

    function toggleLottieInputs() {
        if (lottieUrlRadio.checked) {
            lottieUrlInput.classList.remove('d-none');
            lottieUploadInput.classList.add('d-none');
        } else {
            lottieUrlInput.classList.add('d-none');
            lottieUploadInput.classList.remove('d-none');
        }
    }

    if (lottieUrlRadio) lottieUrlRadio.addEventListener('change', toggleLottieInputs);
    if (lottieUploadRadio) lottieUploadRadio.addEventListener('change', toggleLottieInputs);

    // Custom checkbox functionality
    document.querySelectorAll('.sve-custom-checkbox input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const checkmark = this.nextElementSibling;
            if (this.checked) {
                checkmark.classList.add('checked');
                console.log('Checkbox checked:', this.id);
            } else {
                checkmark.classList.remove('checked');
                console.log('Checkbox unchecked:', this.id);
            }
        });
    });

    // Preview button
    var previewBtn = document.getElementById('sve-preview-btn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const videoUrl = document.getElementById('sve_url').value;
            const thumbnailUrl = document.getElementById('sve_thumbnail_url').value;
            const lottieUrl = document.getElementById('sve_lottie_url').value; // UVEK koristi ovaj input
            const showSkip = document.getElementById('sve_lottie_skip').checked;
            const lazyLoad = document.getElementById('sve_lazy_load').checked;
            const modal = document.getElementById('sve_modal').checked;
            const autoplay = document.getElementById('sve_autoplay').checked;
            const mute = document.getElementById('sve_mute').checked;

            const preview = document.getElementById('sve-preview');
            if (!videoUrl) {
                // Prikaz default placeholdera
                preview.innerHTML = '<span class="dashicons dashicons-format-video preview-icon"></span><div class="preview-text">Click "Preview" to see how your video will look</div>';
                return;
            }
            let embedHtml = '';
            let videoId = '';
            if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
                if (videoUrl.includes('youtu.be/')) {
                    videoId = videoUrl.split('youtu.be/')[1].split(/[?&]/)[0];
                } else {
                    const match = videoUrl.match(/[?&]v=([^&]+)/);
                    if (match) videoId = match[1];
                }
                if (videoId) {
                    let embedUrl = `https://www.youtube.com/embed/${videoId}`;
                    const params = [];
                    if (autoplay) params.push('autoplay=1');
                    if (mute) params.push('mute=1');
                    if (params.length > 0) {
                        embedUrl += '?' + params.join('&');
                    }
                    embedHtml = `<div style=\"max-width:560px;position:relative;\"><iframe width=\"100%\" height=\"315\" src=\"${embedUrl}\" frameborder=\"0\" allowfullscreen></iframe>`;
                }
            }
            if (!embedHtml && thumbnailUrl) {
                embedHtml = `<div style=\"max-width:560px;position:relative;\"><img src=\"${thumbnailUrl}\" alt=\"Video thumbnail\" style=\"width:100%;border-radius:8px;box-shadow:0 2px 8px #0002;\"></div>`;
            } else if (!embedHtml) {
                embedHtml = '<div style=\"max-width:560px;position:relative;height:315px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;\"><div>Video placeholder</div></div>';
            }
            // Dodaj Lottie overlay ako postoji
            if (lottieUrl) {
                embedHtml += `
                <div id=\"sve-lottie-overlay-preview\" style=\"position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:10;\">
                    <div id=\"sve-lottie-preview-preview\" style=\"width:200px;height:200px;\"></div>
                    ${showSkip ? '<button id=\"sve-lottie-skip-preview\" class=\"btn btn-warning\" style=\"position:absolute;top:10px;right:10px;\">Skip</button>' : ''}
                </div>`;
            }
            embedHtml += '</div>';
            preview.innerHTML = embedHtml;
            // Lottie JS
            if (lottieUrl && window.lottie) {
                fetch(lottieUrl)
                    .then(r => r.json())
                    .then(animData => {
                        const anim = window.lottie.loadAnimation({
                            container: document.getElementById('sve-lottie-preview-preview'),
                            renderer: 'svg',
                            loop: false,
                            autoplay: true,
                            animationData: animData
                        });
                        anim.addEventListener('complete', function() {
                            const overlay = document.getElementById('sve-lottie-overlay-preview');
                            if (overlay) overlay.style.display = 'none';
                            // Start video if autoplay is enabled
                            if (autoplay && !modal) {
                                const iframe = document.querySelector('#sve-preview iframe');
                                if (iframe && iframe.src.includes('youtube.com')) {
                                    iframe.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
                                }
                            }
                        });
                        if (showSkip) {
                            const skipBtn = document.getElementById('sve-lottie-skip-preview');
                            if (skipBtn) {
                                skipBtn.onclick = function() {
                                    anim.destroy();
                                    const overlay = document.getElementById('sve-lottie-overlay-preview');
                                    if (overlay) overlay.style.display = 'none';
                                    // Start video if autoplay is enabled
                                    if (autoplay && !modal) {
                                        const iframe = document.querySelector('#sve-preview iframe');
                                        if (iframe && iframe.src.includes('youtube.com')) {
                                            iframe.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
                                        }
                                    }
                                };
                            }
                        }
                    });
            }
        });
    }

    // Generate shortcode button
    var generateBtn = document.getElementById('sve-generate-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const videoUrl = document.getElementById('sve_url').value;
            const thumbnailUrl = document.getElementById('sve_thumbnail_url').value;
            const lottieUrl = document.getElementById('sve_lottie_url').value;
            const showSkip = document.getElementById('sve_lottie_skip').checked;
            const lazyLoad = document.getElementById('sve_lazy_load').checked;
            const modal = document.getElementById('sve_modal').checked;
            const autoplay = document.getElementById('sve_autoplay').checked;
            const mute = document.getElementById('sve_mute').checked;
            const videoTitle = document.getElementById('sve_title').value; // NOVO

            if (!videoUrl) {
                alert('Please enter a video URL first.');
                return;
            }

            let shortcode = '[smart_video_embed';
            // id će biti dodat kasnije kada se dobije iz baze
            if (videoUrl) {
                shortcode += ' url="' + videoUrl + '"';
            }
            if (thumbnailUrl) {
                shortcode += ' thumbnail="' + thumbnailUrl + '"';
            }
            if (lottieUrl) {
                shortcode += ' lottie="' + lottieUrl + '"';
                if (showSkip) {
                    shortcode += ' skip_button="1"';
                }
            }
            if (lazyLoad) {
                shortcode += ' lazy_load="1"';
            }
            if (modal) {
                shortcode += ' modal="1"';
            }
            if (autoplay) {
                shortcode += ' autoplay="1"';
            }
            if (mute) {
                shortcode += ' mute="1"';
            }
            shortcode += ']';

            // AJAX upis u custom tabelu
            var data = {
                action: 'sve_save_video',
                nonce: (typeof sveUpload !== 'undefined') ? sveUpload.nonce : '',
                title: videoTitle, // ISPRAVLJENO
                shortcode: shortcode,
                params: JSON.stringify({
                    url: videoUrl,
                    thumbnail: thumbnailUrl,
                    lottie: lottieUrl,
                    skip_button: showSkip ? 1 : 0,
                    lazy_load: lazyLoad ? 1 : 0,
                    modal: modal ? 1 : 0,
                    autoplay: autoplay ? 1 : 0,
                    mute: mute ? 1 : 0,
                    aspect_ratio: (document.querySelector('input[name="sve_aspect_ratio"]:checked') || {value:'16:9'}).value,
                    max_width: document.getElementById('sve_max_width')?.value || '800px',
                    show_controls: document.getElementById('sve_show_controls')?.checked ? 1 : 0,
                    disable_related: document.getElementById('sve_disable_related')?.checked ? 1 : 0
                })
            };
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(json => {
                if (json.success && json.data && json.data.id) {
                    // Dodaj id kao prvi parametar u shortcode
                    var newShortcode = shortcode.replace('[smart_video_embed', '[smart_video_embed id="' + json.data.id + '"');
                    // AJAX update u bazu
                    var updateData = new FormData();
                    updateData.append('action', 'sve_update_shortcode');
                    updateData.append('nonce', (typeof sveUpload !== 'undefined') ? sveUpload.nonce : '');
                    updateData.append('id', json.data.id);
                    updateData.append('shortcode', newShortcode);
                    fetch(ajaxurl, {
                        method: 'POST',
                        body: updateData
                    });
                    // Prikaži preporučeni shortcode sa ID-em
                    var shortcodeElem = document.getElementById('sve-shortcode-text');
                    var resultElem = document.getElementById('sve-shortcode-result');
                    if (shortcodeElem && resultElem) {
                        shortcodeElem.innerHTML = '[smart_video_embed id="' + json.data.id + '"]';
                        resultElem.classList.remove('d-none');
                        resultElem.querySelector('h6').innerHTML = '<i class="dashicons dashicons-shortcode me-1"></i> Recommended Shortcode (auto-updates everywhere)';
                    }
                } else {
                    alert(json.data && json.data.message ? json.data.message : 'Error saving video.');
                }
            })
            .catch(() => alert('AJAX error.'));
        });
    }
});

function copyShortcode() {
    var codeElem = document.getElementById('sve-shortcode-text');
    if (!codeElem) return;
    var text = codeElem.innerText || codeElem.textContent;
    if (!text) return;
    navigator.clipboard.writeText(text).then(function() {
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
</script> 