<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Ovde će biti pomoćne funkcije za plugin

add_action('wp_ajax_sve_upload_file', function() {
    // Provera nonce-a
    check_ajax_referer('sve_upload_file');
    
    if ( ! current_user_can('upload_files') ) {
        wp_send_json_error(['message' => 'Nemate dozvolu za upload.']);
    }

    if (empty($_FILES['file'])) {
        wp_send_json_error(['message' => 'Fajl nije prosleđen.']);
    }
    
    $file = $_FILES['file'];
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';

    // Debug info
    error_log('SVE Upload - File: ' . print_r($file, true));
    error_log('SVE Upload - Type: ' . $type);

    // Dozvoljene ekstenzije
    $allowed = [
        'thumbnail' => ['jpg','jpeg','png','gif','webp'],
        'lottie'    => ['json']
    ];
    $max_size = 5 * 1024 * 1024; // 5MB

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($type === 'thumbnail' && !in_array($ext, $allowed['thumbnail'])) {
        wp_send_json_error(['message' => 'Dozvoljeni su samo image fajlovi.']);
    }
    if ($type === 'lottie' && $ext !== 'json') {
        wp_send_json_error(['message' => 'Dozvoljen je samo .json fajl za Lottie.']);
    }
    if ($file['size'] > $max_size) {
        wp_send_json_error(['message' => 'Fajl je prevelik (max 5MB).']);
    }

    // Upload folder
    $upload_dir = wp_upload_dir();
    $subdir = '/smart-video-embed';
    $target_dir = $upload_dir['basedir'] . $subdir;
    if ( ! file_exists($target_dir) ) {
        wp_mkdir_p($target_dir);
    }
    
    // Dodaj filter za dozvoljene MIME tipove
    add_filter('upload_mimes', function($mimes) {
        $mimes['json'] = 'application/json';
        return $mimes;
    });
    
    add_filter('upload_dir', function($dirs) use ($subdir) {
        $dirs['subdir'] = $subdir;
        $dirs['path'] = $dirs['basedir'] . $subdir;
        $dirs['url'] = $dirs['baseurl'] . $subdir;
        return $dirs;
    });
    
    $result = wp_handle_upload($file, ['test_form' => false]);
    remove_all_filters('upload_dir');
    remove_all_filters('upload_mimes');

    if (isset($result['error'])) {
        error_log('SVE Upload Error: ' . $result['error']);
        wp_send_json_error(['message' => $result['error']]);
    }
    
    error_log('SVE Upload Success: ' . $result['url']);
    wp_send_json_success(['url' => $result['url']]);
}); 