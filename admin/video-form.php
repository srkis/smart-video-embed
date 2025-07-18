<?php
// $form_values = [ ... , 'mode' => 'add'|'edit' ]
?>
<div class="wrap sve-admin-main">
<div class="p-4 bg-white rounded-4 shadow-sm border" style="min-width:320px;">
<form method="post" id="sve-generator-form">
    <?php if (!empty($form_values['nonce_action'])) wp_nonce_field($form_values['nonce_action']); ?>
    <?php if (!empty($form_values['video_id'])): ?>
        <input type="hidden" name="video_id" value="<?php echo esc_attr($form_values['video_id']); ?>">
    <?php endif; ?>
    <div class="mb-4">
        <label for="sve_title" class="form-label fw-bold">
            <i class="dashicons dashicons-edit me-1"></i>
            Video Title
        </label>
        <input type="text" class="form-control form-control-lg" id="sve_title" name="sve_title" placeholder="Enter video title" required value="<?php echo esc_attr($form_values['title'] ?? ''); ?>">
        <div class="form-text">Enter a descriptive title for this video</div>
    </div>
    <div class="mb-4">
        <label for="sve_url" class="form-label fw-bold">
            <i class="dashicons dashicons-video-alt3 me-1"></i>
            Video URL
        </label>
        <input type="url" class="form-control form-control-lg" id="sve_url" name="sve_url" placeholder="https://youtube.com/watch?v=..." required value="<?php echo esc_attr($form_values['url'] ?? ''); ?>">
        <div class="form-text">Enter a YouTube URL or direct video file link</div>
    </div>
    <div class="mb-4">
        <label class="form-label fw-bold">
            <i class="dashicons dashicons-image-flip-vertical me-1"></i>
            Aspect Ratio
        </label>
        <div class="sve-aspect-ratio-group">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sve_aspect_ratio" id="sve_aspect_16_9" value="16:9" <?php if (($form_values['aspect_ratio'] ?? '16:9') === '16:9') echo 'checked'; ?>>
                <label class="form-check-label" for="sve_aspect_16_9">16:9</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sve_aspect_ratio" id="sve_aspect_4_3" value="4:3" <?php if (($form_values['aspect_ratio'] ?? '') === '4:3') echo 'checked'; ?>>
                <label class="form-check-label" for="sve_aspect_4_3">4:3</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sve_aspect_ratio" id="sve_aspect_1_1" value="1:1" <?php if (($form_values['aspect_ratio'] ?? '') === '1:1') echo 'checked'; ?>>
                <label class="form-check-label" for="sve_aspect_1_1">1:1</label>
            </div>
        </div>
        <div class="form-text">Choose the aspect ratio for the video container</div>
    </div>
    <div class="mb-4">
        <label for="sve_max_width" class="form-label fw-bold">
            <i class="dashicons dashicons-editor-expand me-1"></i>
            Max Width
        </label>
        <input type="text" class="form-control" id="sve_max_width" name="sve_max_width" placeholder="e.g. 800px or 100%" value="<?php echo esc_attr($form_values['max_width'] ?? ''); ?>">
        <div class="form-text">Set the maximum width for the video (px or %)</div>
    </div>
    <div class="mb-4">
        <label class="form-label fw-bold">
            <i class="dashicons dashicons-format-image me-1"></i>
            Custom Thumbnail
        </label>
        <input type="url" class="form-control mb-2" id="sve_thumbnail_url" name="sve_thumbnail_url" placeholder="https://example.com/thumbnail.jpg" value="<?php echo esc_attr($form_values['thumbnail'] ?? ''); ?>">
    </div>
    <div class="mb-4">
        <label class="form-label fw-bold">
            <i class="dashicons dashicons-admin-generic me-1"></i>
            Lottie Animation
        </label>
        <input type="url" class="form-control mb-2" id="sve_lottie_url" name="sve_lottie_url" placeholder="https://example.com/animation.json" value="<?php echo esc_attr($form_values['lottie'] ?? ''); ?>">
    </div>
    <div class="mb-4">
        <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
            <span class="sve-custom-checkbox">
                <input type="checkbox" value="1" id="sve_lottie_skip" name="sve_lottie_skip" <?php if (!empty($form_values['skip_button'])) echo 'checked'; ?> style="display:none;">
                <span class="sve-checkmark"></span>
            </span>
            Show skip button for Lottie animation
        </label>
    </div>
    <div class="mb-4">
        <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
            <span class="sve-custom-checkbox">
                <input type="checkbox" value="1" id="sve_lazy_load" name="sve_lazy_load" <?php if (!empty($form_values['lazy_load'])) echo 'checked'; ?> style="display:none;">
                <span class="sve-checkmark"></span>
            </span>
            Enable lazy loading for better performance
        </label>
    </div>
    <div class="mb-4">
        <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
            <span class="sve-custom-checkbox">
                <input type="checkbox" value="1" id="sve_modal" name="sve_modal" <?php if (!empty($form_values['modal'])) echo 'checked'; ?> style="display:none;">
                <span class="sve-checkmark"></span>
            </span>
            Open video in modal/lightbox
        </label>
    </div>
    <div class="mb-4">
        <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
            <span class="sve-custom-checkbox">
                <input type="checkbox" value="1" id="sve_autoplay" name="sve_autoplay" <?php if (!empty($form_values['autoplay'])) echo 'checked'; ?> style="display:none;">
                <span class="sve-checkmark"></span>
            </span>
            Autoplay video (starts automatically)
        </label>
    </div>
    <div class="mb-4">
        <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
            <span class="sve-custom-checkbox">
                <input type="checkbox" value="1" id="sve_mute" name="sve_mute" <?php if (!empty($form_values['mute'])) echo 'checked'; ?> style="display:none;">
                <span class="sve-checkmark"></span>
            </span>
            Mute video (no sound)
        </label>
    </div>
    <div class="mb-4">
        <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
            <span class="sve-custom-checkbox">
                <input type="checkbox" value="1" id="sve_show_controls" name="sve_show_controls" <?php if (!empty($form_values['show_controls'])) echo 'checked'; ?> style="display:none;">
                <span class="sve-checkmark"></span>
            </span>
            Hide video controls (play, pause, etc.)
        </label>
    </div>
    <div class="mb-4">
        <label class="fw-bold" style="display:flex;align-items:center;gap:0.7em;cursor:pointer;">
            <span class="sve-custom-checkbox">
                <input type="checkbox" value="1" id="sve_disable_related" name="sve_disable_related" <?php if (!empty($form_values['disable_related'])) echo 'checked'; ?> style="display:none;">
                <span class="sve-checkmark"></span>
            </span>
            Disable related videos (YouTube only)
        </label>
    </div>
    <p class="submit mb-0">
        <?php if (!empty($form_values['mode']) && $form_values['mode'] === 'edit'): ?>
            <button type="submit" class="btn btn-success btn-lg px-4">Save Video</button>
            <?php if (!empty($form_values['cancel_url'])): ?>
                <a href="<?php echo esc_url($form_values['cancel_url']); ?>" class="btn btn-secondary btn-lg ms-2">Cancel</a>
            <?php endif; ?>
        <?php else: ?>
            <button type="button" class="btn btn-primary btn-sm px-4" id="sve-preview-btn">
                <i class="dashicons dashicons-visibility me-1"></i>
                Preview
            </button>
            <button type="button" class="btn btn-success btn-sm px-4" id="sve-generate-btn">
                <i class="dashicons dashicons-shortcode me-1"></i>
                Generate Shortcode
            </button>
        <?php endif; ?>
    </p>
</form>
</div>
</div>
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