<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function smart_video_embed_shortcode($atts) {
    global $wpdb;
    $db_params = [];
    if (!empty($atts['id'])) {
        $table = $wpdb->prefix . 'sve_videos';
        $video = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($atts['id'])));
        if ($video) {
            $db_params = json_decode($video->params, true);
        }
    }
    // Merge: shortcode params have priority, then db params, then defaults
    $atts = shortcode_atts(array(
        'url' => '',
        'thumbnail' => '',
        'lottie' => '',
        'skip_button' => '0',
        'lazy_load' => '1',
        'modal' => '0',
        'autoplay' => '0',
        'mute' => '0',
        'type' => '',
        'theme' => '',
        'aspect_ratio' => '16:9',
        'max_width' => '800px'
    ), array_merge($db_params, $atts));

    if (empty($atts['url'])) {
        return '<p>Error: Video URL is required.</p>';
    }

    $video_url = esc_url($atts['url']);
    $thumbnail_url = !empty($atts['thumbnail']) ? esc_url($atts['thumbnail']) : '';
    $lottie_url = !empty($atts['lottie']) ? esc_url($atts['lottie']) : '';
    $skip_button = !empty($atts['skip_button']) && $atts['skip_button'] != '0' && $atts['skip_button'] !== false;
    $lazy_load = $atts['lazy_load'] === '1';
    $modal = !empty($atts['modal']) && $atts['modal'] != '0' && $atts['modal'] !== false;
    $autoplay = !empty($atts['autoplay']) && $atts['autoplay'] != '0' && $atts['autoplay'] !== false;
    $mute = !empty($atts['mute']) && $atts['mute'] != '0' && $atts['mute'] !== false;
    $theme = !empty($atts['theme']) ? sanitize_text_field($atts['theme']) : '';
    $is_videojs = ($atts['type'] === 'videojs' || $theme);

    // Generate unique ID for this video instance
    $video_id = 'sve-video-' . uniqid();

    // === VIDEO.JS MODE ===
    if ($is_videojs) {
        $aspect_ratio = !empty($atts['aspect_ratio']) ? $atts['aspect_ratio'] : '16:9';
        $max_width = !empty($atts['max_width']) ? $atts['max_width'] : '800px';
        ob_start();
        ?>
        <div class="sve-videojs-container" id="<?php echo $video_id; ?>" style="max-width:<?php echo esc_attr($max_width); ?>;margin:auto;position:relative;">
            <video id="videojs-<?php echo $video_id; ?>" class="video-js vjs-big-play-centered<?php echo $theme ? ' vjs-theme-' . esc_attr($theme) : ''; ?>" controls preload="auto" style="width:100%;" poster="<?php echo esc_attr($thumbnail_url); ?>">
                <?php if (preg_match('/(youtube\.com|youtu\.be)/i', $video_url)): ?>
                    <source src="<?php echo esc_url($video_url); ?>" type="video/youtube">
                <?php elseif (preg_match('/vimeo\.com/i', $video_url)): ?>
                    <source src="<?php echo esc_url($video_url); ?>" type="video/vimeo">
                <?php else: ?>
                    <source src="<?php echo esc_url($video_url); ?>">
                <?php endif; ?>
            </video>
            <?php if ($lottie_url): ?>
            <div class="sve-videojs-lottie-overlay" id="lottie-overlay-<?php echo $video_id; ?>" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:10;">
                <div id="lottie-<?php echo $video_id; ?>" style="width:200px;height:200px;"></div>
                <?php if ($skip_button): ?>
                <button type="button" class="sve-videojs-lottie-skip">Skip</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var player = window.videojs && window.videojs('videojs-<?php echo $video_id; ?>', {
                techOrder: ['youtube', 'vimeo', 'html5'],
                controls: true,
                preload: 'auto',
                fluid: true,
                aspectRatio: <?php echo json_encode($aspect_ratio); ?>,
                autoplay: <?php echo $autoplay ? 'true' : 'false'; ?>,
                muted: <?php echo $mute ? 'true' : 'false'; ?>
            });
            // Theme switch (enable only selected theme)
            var theme = <?php echo json_encode($theme); ?>;
            var themeIds = ['city','fantasy','forest','sea','classic'];
            themeIds.forEach(function(id) {
                var link = document.getElementById('vjs-theme-' + id) || document.querySelector('link[href*="/themes@1.0.1/dist/' + id + '/index.css"]');
                if (link) link.disabled = (id !== theme);
            });
            if (player && theme) {
                var el = player.el();
                themeIds.forEach(function(id) { el.classList.remove('vjs-theme-' + id); });
                el.classList.add('vjs-theme-' + theme);
            }
            // Lottie overlay
            <?php if ($lottie_url): ?>
            var lottieOverlay = document.getElementById('lottie-overlay-<?php echo $video_id; ?>');
            var lottieContainer = document.getElementById('lottie-<?php echo $video_id; ?>');
            if (window.lottie && lottieContainer) {
                fetch(<?php echo json_encode($lottie_url); ?>)
                    .then(r => r.json())
                    .then(animData => {
                        var anim = window.lottie.loadAnimation({
                            container: lottieContainer,
                            renderer: 'svg',
                            loop: false,
                            autoplay: true,
                            animationData: animData
                        });
                        anim.addEventListener('complete', function() {
                            if (lottieOverlay) lottieOverlay.style.display = 'none';
                            // Start video if autoplay is enabled
                            if (<?php echo $autoplay ? 'true' : 'false'; ?> && player) {
                                player.play();
                            }
                        });
                        var skipBtn = lottieOverlay ? lottieOverlay.querySelector('.sve-videojs-lottie-skip') : null;
                        if (skipBtn) {
                            skipBtn.onclick = function() {
                                anim.destroy();
                                if (lottieOverlay) lottieOverlay.style.display = 'none';
                                // Start video if autoplay is enabled
                                if (<?php echo $autoplay ? 'true' : 'false'; ?> && player) {
                                    player.play();
                                }
                            };
                        }
                    });
            }
            <?php endif; ?>
        });
        </script>
        <?php
        return ob_get_clean();
    }

    // === CLASSIC/DEFAULT MODE ===
    // Check if it's a YouTube URL
    $is_youtube = preg_match('/(?:youtube\\.com\/watch\\?v=|youtu\\.be\/|youtube\\.com\/embed\/)([^&\\n?#]+)/', $video_url, $matches);
    
    if ($is_youtube) {
        $youtube_id = $matches[1];
        $embed_url = 'https://www.youtube.com/embed/' . $youtube_id;
        
        // Add autoplay and mute parameters for YouTube
        $youtube_params = [];
        if ($autoplay) {
            $youtube_params[] = 'autoplay=1';
        }
        if ($mute) {
            $youtube_params[] = 'mute=1';
        }
        if (!empty($youtube_params)) {
            $embed_url .= '?' . implode('&', $youtube_params);
        }
        
        $default_thumbnail = 'https://img.youtube.com/vi/' . $youtube_id . '/maxresdefault.jpg';
        if (empty($thumbnail_url)) {
            $thumbnail_url = $default_thumbnail;
        }
    } else {
        $embed_url = $video_url;
    }

    $aspect_ratio = !empty($atts['aspect_ratio']) ? $atts['aspect_ratio'] : '16:9';
    $max_width = !empty($atts['max_width']) ? $atts['max_width'] : '800px';
    // Izračunaj aspect-ratio vrednost za CSS
    $aspect_css = '16/9';
    if ($aspect_ratio === '4:3') $aspect_css = '4/3';
    if ($aspect_ratio === '1:1') $aspect_css = '1/1';

    ob_start();
    ?>
    <div class="sve-video-container" id="<?php echo $video_id; ?>" data-modal="<?php echo $modal ? '1' : '0'; ?>" data-lottie="<?php echo $lottie_url ? '1' : '0'; ?>">
        <div class="sve-video-inner" style="position:relative;max-width:<?php echo esc_attr($max_width); ?>;margin:auto;">
            <!-- Video element - uvek prikazan, sa Lottie overlay-om ako postoji -->
            <div class="sve-video-iframe" id="video-<?php echo $video_id; ?>" style="aspect-ratio:<?php echo $aspect_css; ?>;border-radius:12px;overflow:hidden;">
                <iframe src="<?php echo $embed_url; ?>" frameborder="0" allowfullscreen style="width:100%;height:100%;border-radius:12px;"></iframe>
            </div>
            
            <!-- Thumbnail - prikazuje se nakon Lottie animacije -->
            <?php if ($modal): ?>
            <div class="sve-video-thumbnail" id="thumb-<?php echo $video_id; ?>" style="display:none;cursor:pointer;border-radius:12px;overflow:hidden;">
                <?php if ($thumbnail_url): ?>
                    <img src="<?php echo $thumbnail_url; ?>" alt="Video thumbnail" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <div style="background:#f0f0f0;color: #666; text-align: center; width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                        <span class="dashicons dashicons-video-alt3" style="font-size: 48px; display: block; margin-bottom: 16px;"></span>
                        <div style="font-size: 16px;">Click to open video</div>
                    </div>
                <?php endif; ?>
                <div class="sve-play-overlay" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.7);border-radius:50%;width:80px;height:80px;display:flex;align-items:center;justify-content:center;">
                    <span class="dashicons dashicons-controls-play" style="color:white;font-size:32px;margin-left:4px;"></span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Lottie Overlay - na video elementu -->
            <?php if ($lottie_url): ?>
            <div class="sve-lottie-overlay" id="lottie-overlay-<?php echo $video_id; ?>" style="display:flex;position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);align-items:center;justify-content:center;z-index:10;">
                <div id="lottie-<?php echo $video_id; ?>" style="width:200px;height:200px;display:block;"></div>
                <?php if ($skip_button): ?>
                <button type="button" class="sve-lottie-skip-btn" style="position:absolute;top:10px;right:10px;background:#ffc107;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;color:#000;">Skip</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($modal): ?>
        <!-- Modal Container -->
        <div class="sve-modal-frontend" id="modal-<?php echo $video_id; ?>" tabindex="-1">
            <div class="sve-modal-content-frontend">
                <button type="button" class="sve-modal-close-frontend" aria-label="Close">×</button>
                <iframe src="<?php echo $embed_url; ?>" frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    // Lottie loader
    <?php if ($lottie_url): ?>
    (function() {
        var lottieContainer = document.getElementById('lottie-<?php echo $video_id; ?>');
        var lottieOverlay = document.getElementById('lottie-overlay-<?php echo $video_id; ?>');
        var skipBtn = lottieOverlay ? lottieOverlay.querySelector('.sve-lottie-skip-btn') : null;
        var isModal = <?php echo $modal ? 'true' : 'false'; ?>;
        var thumbElement = document.getElementById('thumb-<?php echo $video_id; ?>');
        var videoElement = document.getElementById('video-<?php echo $video_id; ?>');
        
        console.log('Lottie initialization for <?php echo $video_id; ?>:', {
            lottieUrl: '<?php echo $lottie_url; ?>',
            lottieContainer: lottieContainer,
            lottieOverlay: lottieOverlay,
            skipBtn: skipBtn,
            isModal: isModal,
            thumbElement: thumbElement,
            videoElement: videoElement
        });
        
        function showThumbnail() {
            console.log('showThumbnail called for <?php echo $video_id; ?>');
            if (lottieOverlay) lottieOverlay.style.display = 'none';
            if (isModal && thumbElement) {
                if (videoElement) {
                    videoElement.style.display = 'none';
                    videoElement.style.zIndex = '1';
                }
                thumbElement.style.display = 'block';
                thumbElement.style.zIndex = '20';
                // Uklonjeno: thumbElement.style.position = 'absolute';
                // Dodaj handler za klik na thumbnail (otvara modal)
                thumbElement.onclick = function(e) {
                    e.preventDefault();
                    var modalElem = document.getElementById('modal-<?php echo $video_id; ?>');
                    if (modalElem) {
                        modalElem.classList.add('active');
                        document.body.style.overflow = 'hidden';
                        var closeBtn = modalElem.querySelector('.sve-modal-close-frontend');
                        if (closeBtn) closeBtn.focus();
                    }
                };
                console.log('Showing thumbnail for modal mode');
            } else if (isModal && !thumbElement && videoElement) {
                // Fallback: ako nema thumbnaila, prikaži video
                videoElement.style.display = 'block';
                videoElement.style.zIndex = '1';
                console.log('No thumbnail found, showing video as fallback');
            }
        }
        
        function runLottie(tries) {
            tries = tries || 0;
            if (typeof lottie !== 'undefined' && lottieContainer) {
                console.log('Loading Lottie animation from: <?php echo $lottie_url; ?>');
                try {
                    var anim = lottie.loadAnimation({
                        container: lottieContainer,
                        renderer: 'svg',
                        loop: false,
                        autoplay: true,
                        path: '<?php echo $lottie_url; ?>'
                    });
                    console.log('Lottie animation loaded:', anim);
                    anim.addEventListener('complete', function() {
                        console.log('Lottie animation completed');
                        showThumbnail();
                        // Start video if autoplay is enabled
                        if (<?php echo $autoplay ? 'true' : 'false'; ?> && !isModal) {
                            var videoElement = document.getElementById('video-<?php echo $video_id; ?>');
                            if (videoElement) {
                                var iframe = videoElement.querySelector('iframe');
                                if (iframe) {
                                    // For YouTube, we need to send a postMessage to start autoplay
                                    if (iframe.src.includes('youtube.com')) {
                                        iframe.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
                                    }
                                }
                            }
                        }
                    });
                    if (skipBtn) {
                        skipBtn.addEventListener('click', function() {
                            console.log('Skip button clicked');
                            anim.destroy();
                            showThumbnail();
                            // Start video if autoplay is enabled
                            if (<?php echo $autoplay ? 'true' : 'false'; ?> && !isModal) {
                                var videoElement = document.getElementById('video-<?php echo $video_id; ?>');
                                if (videoElement) {
                                    var iframe = videoElement.querySelector('iframe');
                                    if (iframe) {
                                        // For YouTube, we need to send a postMessage to start autoplay
                                        if (iframe.src.includes('youtube.com')) {
                                            iframe.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
                                        }
                                    }
                                }
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error loading Lottie animation:', error);
                    showThumbnail();
                }
            } else {
                if (tries < 20) {
                    setTimeout(function() { runLottie(tries + 1); }, 100);
                } else {
                    showThumbnail();
                }
            }
        }
        runLottie();
    })();
    <?php endif; ?>
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('smart_video_embed', 'smart_video_embed_shortcode');

// Always enqueue Lottie JS on frontend if shortcode is present
add_action('wp_enqueue_scripts', function() {
    global $post;
    if (is_singular() && isset($post->post_content) && strpos($post->post_content, '[smart_video_embed') !== false) {
        wp_enqueue_script('lottie-web', 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js', [], '5.12.2', true);
    }
}); 