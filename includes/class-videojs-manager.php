<?php
// SVE_VideoJS_Manager - Video.js integration manager
// All code comments and names in English, as per user preference

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SVE_VideoJS_Manager {
    public function __construct() {
        // Hook for admin menu
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        // Enqueue assets only on our admin page
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        // Dodaj sve Video.js teme u <head> sa disabled na admin page load
        add_action( 'admin_head', [ $this, 'output_videojs_theme_links' ] );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_bootstrap' ] );
    }

    /**
     * Register the Video.js admin menu
     */
    public function register_admin_menu() {
        add_submenu_page(
            'sve-settings', // Parent slug
            __( 'Video.js Embed', 'smart-video-embed' ),
            __( 'Video.js Embed', 'smart-video-embed' ),
            'manage_options',
            'sve-videojs-embed',
            [ $this, 'render_admin_page' ],
            2 // Position after All Videos
        );
    }

    /**
     * Enqueue JS/CSS only on Video.js admin page
     */
    public function enqueue_admin_assets( $hook ) {
        if ( $hook !== 'toplevel_page_sve-videojs-embed' && $hook !== 'smart-video-embed_page_sve-videojs-embed' && $hook !== 'smart-video-embed_page_sve-edit-video' ) {
            return;
        }
        // Bootstrap
        wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', [], '5.3.3' );
        // Video.js core
        wp_enqueue_style( 'videojs', 'https://cdn.jsdelivr.net/npm/video.js@8.10.0/dist/video-js.min.css', [], '8.10.0' );
        wp_enqueue_script( 'videojs', 'https://cdn.jsdelivr.net/npm/video.js@8.10.0/dist/video.min.js', [], '8.10.0', true );
        // Video.js YouTube/Vimeo plugins
        wp_enqueue_script( 'videojs-youtube', 'https://cdn.jsdelivr.net/npm/videojs-youtube@3.0.1/dist/Youtube.min.js', [ 'videojs' ], '3.0.1', true );
        wp_enqueue_script( 'videojs-vimeo', 'https://cdn.jsdelivr.net/npm/videojs-vimeo@2.0.2/dist/videojs-vimeo.min.js', [ 'videojs' ], '2.0.2', true );
        // Custom admin CSS/JS
        wp_enqueue_style( 'sve-style', SVE_PLUGIN_URL . 'public/assets/css/style.css', [], SVE_VERSION );
        wp_enqueue_style( 'sve-videojs-admin', SVE_PLUGIN_URL . 'public/assets/css/videojs-admin.css', [], SVE_VERSION );
        wp_enqueue_script( 'sve-videojs-admin', SVE_PLUGIN_URL . 'public/assets/js/videojs-admin.js', [ 'videojs', 'videojs-youtube', 'videojs-vimeo' ], SVE_VERSION, true );
        // Lokalizuj sveUpload za AJAX
        wp_localize_script( 'sve-videojs-admin', 'sveUpload', [
            'nonce' => wp_create_nonce('sve_upload_file'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ] );
    }


    public function enqueue_bootstrap( $hook ) {
        // Samo na našem Video.js admin page-u i edit stranici
        if ( $hook !== 'toplevel_page_sve-videojs-embed' && $hook !== 'smart-video-embed_page_sve-videojs-embed' && $hook !== 'smart-video-embed_page_sve-edit-video' ) {
            return;
        }
    
        wp_enqueue_style(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
            [],
            '5.3.3'
        );
    
        wp_enqueue_script(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
            [],
            '5.3.3',
            true
        );
    }


    public function output_videojs_theme_links() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
    
        if ( ! isset($_GET['page']) || $_GET['page'] !== 'sve-videojs-embed' ) {
            return;
        }
    
        echo '<link rel="stylesheet" id="vjs-theme-city" href="https://unpkg.com/@videojs/themes@1.0.1/dist/city/index.css" disabled="disabled" />' . "\n";
        echo '<link rel="stylesheet" id="vjs-theme-fantasy" href="https://unpkg.com/@videojs/themes@1.0.1/dist/fantasy/index.css" disabled="disabled" />' . "\n";
        echo '<link rel="stylesheet" id="vjs-theme-forest" href="https://unpkg.com/@videojs/themes@1.0.1/dist/forest/index.css" disabled="disabled" />' . "\n";
        echo '<link rel="stylesheet" id="vjs-theme-sea" href="https://unpkg.com/@videojs/themes@1.0.1/dist/sea/index.css" disabled="disabled" />' . "\n";
        echo '<link rel="stylesheet" id="vjs-theme-classic" href="https://unpkg.com/@videojs/themes@1.0.1/dist/classic/index.css" disabled="disabled" />' . "\n";
    }
    

    /**
     * Render the Video.js admin page (skeleton form)
     */
    public function render_admin_page() {
        ?>
        <div class="wrap sve-admin-main" style="padding-top:32px;">
            <div class="row">
                <div class="col-12 mb-4">
                    <h1 class="display-5 fw-bold mb-2" style="letter-spacing:0.01em;">Video.js Embed</h1>
                    <div class="text-muted mb-3" style="font-size:1.15rem;max-width:700px;">
                        Easily generate advanced Video.js shortcodes with custom themes, thumbnails, Lottie animations, lazy load, modal, and more. All options are fully compatible with Video.js player.
                    </div>
                    <hr>
                </div>
            </div>
            <div class="row align-items-start">
                <!-- LEVA KOLONA: FORMA -->
                <div class="col-lg-7 col-md-12 mb-4">
                    <div class="p-4 bg-white rounded-4 shadow-sm border" style="min-width:320px;">
                        <form method="post" id="sve-videojs-form">
                                <div class="mb-4">
                                    <label for="sve_videojs_title" class="form-label fw-bold">
                                        <i class="dashicons dashicons-edit me-1"></i>
                                        Video Title
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="sve_videojs_title" name="sve_videojs_title" placeholder="Enter video title" required value="">
                                    <div class="form-text">Enter a descriptive title for this video</div>
                                </div>
                                <div class="mb-4">
                                    <label for="sve_videojs_url" class="form-label fw-bold">
                                        <i class="dashicons dashicons-video-alt3 me-1"></i>
                                        Video URL
                                    </label>
                                    <input type="url" class="form-control form-control-lg" id="sve_videojs_url" name="sve_videojs_url" placeholder="https://youtube.com/watch?v=..." required value="">
                                    <div class="form-text">Enter a YouTube, Vimeo or direct MP4 video link</div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="dashicons dashicons-format-image me-1"></i>
                                        Custom Thumbnail
                                    </label>
                                    <input type="url" class="form-control mb-2" id="sve_videojs_thumbnail" name="sve_videojs_thumbnail" placeholder="https://example.com/thumbnail.jpg" value="">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="dashicons dashicons-admin-generic me-1"></i>
                                        Lottie Animation
                                    </label>
                                    <input type="url" class="form-control mb-2" id="sve_videojs_lottie" name="sve_videojs_lottie" placeholder="https://example.com/animation.json" value="">
                                </div>
                                <div class="mb-4">
                                    <label for="sve_videojs_theme" class="form-label fw-bold">
                                        <i class="dashicons dashicons-admin-appearance me-1"></i>
                                        Player Theme
                                    </label>
                                    <select id="sve_videojs_theme" name="sve_videojs_theme" class="form-select form-select-lg">
                                        <option value="classic">Classic</option>
                                        <option value="city">City</option>
                                        <option value="fantasy">Fantasy</option>
                                        <option value="forest">Forest</option>
                                        <option value="sea">Sea</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                                        <span class="sve-custom-checkbox">
                                            <input type="checkbox" value="1" id="sve_videojs_skip" name="sve_videojs_skip" style="display:none;">
                                            <span class="sve-checkmark"></span>
                                        </span>
                                        Show skip button for Lottie animation
                                    </label>
                                </div>
                                <div class="mb-4">
                                    <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                                        <span class="sve-custom-checkbox">
                                            <input type="checkbox" value="1" id="sve_videojs_lazy" name="sve_videojs_lazy" style="display:none;">
                                            <span class="sve-checkmark"></span>
                                        </span>
                                        Enable lazy loading for better performance
                                    </label>
                                </div>
                                <div class="mb-4">
                                    <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                                        <span class="sve-custom-checkbox">
                                            <input type="checkbox" value="1" id="sve_videojs_modal" name="sve_videojs_modal" style="display:none;">
                                            <span class="sve-checkmark"></span>
                                        </span>
                                        Open video in modal/lightbox
                                    </label>
                                </div>
                                <div class="mb-4">
                                    <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                                        <span class="sve-custom-checkbox">
                                            <input type="checkbox" value="1" id="sve_videojs_autoplay" name="sve_videojs_autoplay" style="display:none;">
                                            <span class="sve-checkmark"></span>
                                        </span>
                                        Autoplay video on load
                                    </label>
                                </div>
                                <div class="mb-4">
                                    <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                                        <span class="sve-custom-checkbox">
                                            <input type="checkbox" value="1" id="sve_videojs_mute" name="sve_videojs_mute" style="display:none;">
                                            <span class="sve-checkmark"></span>
                                        </span>
                                        Mute video on load (required for autoplay in some browsers)
                                    </label>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="dashicons dashicons-image-flip-vertical me-1"></i>
                                        Aspect Ratio
                                    </label>
                                    <div class="sve-aspect-ratio-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sve_videojs_aspect_ratio" id="sve_videojs_aspect_16_9" value="16:9" checked>
                                            <label class="form-check-label" for="sve_videojs_aspect_16_9">16:9</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sve_videojs_aspect_ratio" id="sve_videojs_aspect_4_3" value="4:3">
                                            <label class="form-check-label" for="sve_videojs_aspect_4_3">4:3</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sve_videojs_aspect_ratio" id="sve_videojs_aspect_1_1" value="1:1">
                                            <label class="form-check-label" for="sve_videojs_aspect_1_1">1:1</label>
                                        </div>
                                    </div>
                                    <div class="form-text">Choose the aspect ratio for the Video.js player</div>
                                </div>
                                <div class="mb-4">
                                    <label for="sve_videojs_max_width" class="form-label fw-bold">
                                        <i class="dashicons dashicons-editor-expand me-1"></i>
                                        Max Width
                                    </label>
                                    <input type="text" class="form-control" id="sve_videojs_max_width" name="sve_videojs_max_width" placeholder="e.g. 800px or 100%" value="800px">
                                    <div class="form-text">Set the maximum width for the Video.js player (px or %)</div>
                                </div>
                                <div class="mb-4">
                                    <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                                        <span class="sve-custom-checkbox">
                                            <input type="checkbox" value="1" id="sve_videojs_hide_controls" name="sve_videojs_hide_controls" style="display:none;">
                                            <span class="sve-checkmark"></span>
                                        </span>
                                        Hide video controls (play, pause, etc.)
                                    </label>
                                </div>
                                <div class="mb-4">
                                    <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                                        <span class="sve-custom-checkbox">
                                            <input type="checkbox" value="1" id="sve_videojs_disable_related" name="sve_videojs_disable_related" style="display:none;">
                                            <span class="sve-checkmark"></span>
                                        </span>
                                        Disable related videos (YouTube only)
                                    </label>
                                </div>
                                <p class="submit mb-0">
                                    <button type="button" class="btn btn-primary btn-sm px-4" id="sve-videojs-preview-btn">
                                        <i class="dashicons dashicons-visibility me-1"></i>
                                        Preview
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm px-4" id="sve-videojs-generate-btn">
                                        <i class="dashicons dashicons-shortcode me-1"></i>
                                        Generate Shortcode
                                    </button>
                                </p>
                            </form>
                        </div>
                    <!-- Shortcode Result -->
                    <div id="sve-videojs-shortcode-result" class="alert alert-info d-none mt-4">
                        <h6 class="alert-heading">
                            <i class="dashicons dashicons-shortcode me-1"></i>
                            Generated Shortcode
                        </h6>
                        <div class="bg-light p-3 rounded mt-2">
                            <code id="sve-videojs-shortcode-text"></code>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-copy-shortcode" onclick="copyVideojsShortcode()">
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
                            <div id="sve-videojs-preview" class="w-100 text-center">
                                <span class="dashicons dashicons-format-video preview-icon"></span>
                                <div class="preview-text">Click \"Preview\" to see how your Video.js player will look</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <style>
.sve-aspect-ratio-group .form-check-input[type="radio"] {
    vertical-align: middle !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    position: relative !important;
    top: 0.18em !important;
    box-shadow: none !important;
}
.sve-aspect-ratio-group .form-check-label {
    vertical-align: middle !important;
    margin-bottom: 0 !important;
    margin-left: 0.2em !important;
    font-weight: 600 !important;
    font-size: 1.08em !important;
    line-height: 1.1 !important;
    display: flex !important;
    align-items: center !important;
    height: 1.7em !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}
</style>
        <?php
    }
} 