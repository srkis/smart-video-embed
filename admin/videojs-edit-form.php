<?php
// Video.js Edit Form
// This form is used for editing existing Video.js videos
// Video data is passed from the parent context ($video, $params, $is_videojs)



// Extract Video.js specific parameters from the parsed params
$videojs_url = $params['url'] ?? '';
$videojs_thumbnail = $params['thumbnail'] ?? '';
$videojs_lottie = $params['lottie'] ?? '';
$videojs_theme = $params['theme'] ?? 'classic';
$videojs_skip = ($params['skip_button'] ?? '') == '1';
$videojs_lazy = ($params['lazy_load'] ?? '') == '1';
$videojs_modal = ($params['modal'] ?? '') == '1';
$videojs_autoplay = ($params['autoplay'] ?? '') == '1';
$videojs_mute = ($params['mute'] ?? '') == '1';
?>

<div class="p-4 bg-white rounded-4 shadow-sm border" style="min-width:320px;">
    <form method="post" action="<?php echo admin_url('admin.php?page=sve-edit-video&id=' . $video->id); ?>" id="sve-videojs-edit-form">
        <?php wp_nonce_field('sve_edit_video_' . $video->id); ?>
        <input type="hidden" name="video_id" value="<?php echo esc_attr($video->id); ?>">
        
        <div class="mb-4">
            <label for="sve_title" class="form-label fw-bold">
                <i class="dashicons dashicons-edit me-1"></i>
                Video Title
            </label>
            <input type="text" class="form-control form-control-lg" id="sve_title" name="sve_title" placeholder="Enter video title" required value="<?php echo esc_attr($video->title ?? ''); ?>">
            <div class="form-text">Enter a descriptive title for this video</div>
        </div>
        
        <div class="mb-4">
            <label for="sve_videojs_url" class="form-label fw-bold">
                <i class="dashicons dashicons-video-alt3 me-1"></i>
                Video URL
            </label>
            <input type="url" class="form-control form-control-lg" id="sve_videojs_url" name="sve_videojs_url" placeholder="https://youtube.com/watch?v=..." required value="<?php echo esc_attr($videojs_url); ?>">
            <div class="form-text">Enter a YouTube, Vimeo or direct MP4 video link</div>
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-bold">
                <i class="dashicons dashicons-image-flip-vertical me-1"></i>
                Aspect Ratio
            </label>
            <div class="sve-aspect-ratio-group">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sve_videojs_aspect_ratio" id="sve_videojs_aspect_16_9" value="16:9" <?php if (($params['aspect_ratio'] ?? '16:9') === '16:9') echo 'checked'; ?>>
                    <label class="form-check-label" for="sve_videojs_aspect_16_9">16:9</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sve_videojs_aspect_ratio" id="sve_videojs_aspect_4_3" value="4:3" <?php if (($params['aspect_ratio'] ?? '') === '4:3') echo 'checked'; ?>>
                    <label class="form-check-label" for="sve_videojs_aspect_4_3">4:3</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sve_videojs_aspect_ratio" id="sve_videojs_aspect_1_1" value="1:1" <?php if (($params['aspect_ratio'] ?? '') === '1:1') echo 'checked'; ?>>
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
            <input type="text" class="form-control" id="sve_videojs_max_width" name="sve_videojs_max_width" placeholder="e.g. 800px or 100%" value="<?php echo esc_attr($params['max_width'] ?? '800px'); ?>">
            <div class="form-text">Set the maximum width for the Video.js player (px or %)</div>
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-bold">
                <i class="dashicons dashicons-format-image me-1"></i>
                Custom Thumbnail
            </label>
            <input type="url" class="form-control mb-2" id="sve_videojs_thumbnail" name="sve_videojs_thumbnail" placeholder="https://example.com/thumbnail.jpg" value="<?php echo esc_attr($videojs_thumbnail); ?>">
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-bold">
                <i class="dashicons dashicons-admin-generic me-1"></i>
                Lottie Animation
            </label>
            <input type="url" class="form-control mb-2" id="sve_videojs_lottie" name="sve_videojs_lottie" placeholder="https://example.com/animation.json" value="<?php echo esc_attr($videojs_lottie); ?>">
        </div>
        
        <div class="mb-4">
            <label for="sve_videojs_theme" class="form-label fw-bold">
                <i class="dashicons dashicons-admin-appearance me-1"></i>
                Player Theme
            </label>
            <select id="sve_videojs_theme" name="sve_videojs_theme" class="form-select form-select-lg">
                <option value="classic" <?php selected($videojs_theme, 'classic'); ?>>Classic</option>
                <option value="city" <?php selected($videojs_theme, 'city'); ?>>City</option>
                <option value="fantasy" <?php selected($videojs_theme, 'fantasy'); ?>>Fantasy</option>
                <option value="forest" <?php selected($videojs_theme, 'forest'); ?>>Forest</option>
                <option value="sea" <?php selected($videojs_theme, 'sea'); ?>>Sea</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                <span class="sve-custom-checkbox">
                    <input type="checkbox" value="1" id="sve_videojs_skip" name="sve_videojs_skip" <?php checked($videojs_skip); ?> style="display:none;">
                    <span class="sve-checkmark"></span>
                </span>
                Show skip button for Lottie animation
            </label>
        </div>
        
        <div class="mb-4">
            <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                <span class="sve-custom-checkbox">
                    <input type="checkbox" value="1" id="sve_videojs_lazy" name="sve_videojs_lazy" <?php checked($videojs_lazy); ?> style="display:none;">
                    <span class="sve-checkmark"></span>
                </span>
                Enable lazy loading for better performance
            </label>
        </div>
        
        <div class="mb-4">
            <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                <span class="sve-custom-checkbox">
                    <input type="checkbox" value="1" id="sve_videojs_modal" name="sve_videojs_modal" <?php checked($videojs_modal); ?> style="display:none;">
                    <span class="sve-checkmark"></span>
                </span>
                Open video in modal/lightbox
            </label>
        </div>
        <div class="mb-4">
            <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                <span class="sve-custom-checkbox">
                    <input type="checkbox" value="1" id="sve_videojs_autoplay" name="sve_videojs_autoplay" <?php checked($videojs_autoplay); ?> style="display:none;">
                    <span class="sve-checkmark"></span>
                </span>
                Autoplay video on load
            </label>
        </div>
        <div class="mb-4">
            <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                <span class="sve-custom-checkbox">
                    <input type="checkbox" value="1" id="sve_videojs_mute" name="sve_videojs_mute" <?php checked($videojs_mute); ?> style="display:none;">
                    <span class="sve-checkmark"></span>
                </span>
                Mute video on load (required for autoplay in some browsers)
            </label>
        </div>
        <div class="mb-4">
            <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                <span class="sve-custom-checkbox">
                    <input type="checkbox" value="1" id="sve_videojs_hide_controls" name="sve_videojs_hide_controls" <?php if (!empty($params['hide_controls'])) echo 'checked'; ?> style="display:none;">
                    <span class="sve-checkmark"></span>
                </span>
                Hide video controls (play, pause, etc.)
            </label>
        </div>
        <div class="mb-4">
            <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
                <span class="sve-custom-checkbox">
                    <input type="checkbox" value="1" id="sve_videojs_disable_related" name="sve_videojs_disable_related" <?php if (!empty($params['disable_related'])) echo 'checked'; ?> style="display:none;">
                    <span class="sve-checkmark"></span>
                </span>
                Disable related videos (YouTube only)
            </label>
        </div>
        
        <p class="submit mb-0">
            <button type="submit" class="btn btn-success btn-lg px-4">Save Video.js Video</button>
            <a href="<?php echo admin_url('admin.php?page=sve-videos'); ?>" class="btn btn-secondary btn-lg ms-2">Cancel</a>
        </p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom checkbox UX for Video.js edit form
    document.querySelectorAll('.sve-custom-checkbox input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const checkmark = this.nextElementSibling;
            if (this.checked) {
                checkmark.classList.add('checked');
            } else {
                checkmark.classList.remove('checked');
            }
        });
        
        // Initialize checkbox state
        const checkmark = checkbox.nextElementSibling;
        if (checkbox.checked) {
            checkmark.classList.add('checked');
        }
    });
});
</script> 

<style>
.sve-aspect-ratio-group {
    display: flex;
    align-items: center;
    gap: 2.2em;
}
.sve-aspect-ratio-group .form-check {
    display: flex;
    align-items: center;
    margin-bottom: 0;
    gap: 0.3em;
}
.sve-aspect-ratio-group .form-check-label {
    margin-bottom: 0;
    margin-left: 0.2em;
    vertical-align: middle;
    line-height: 1.1;
    font-weight: 600;
    font-size: 1.08em;
}
.sve-aspect-ratio-group .form-check-input[type="radio"] {
    margin-top: 0;
    margin-right: 0;
    position: relative;
    top: 0;
}
</style> 