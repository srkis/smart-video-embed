<?php
/*
Plugin Name: Smart Video Embed
Description: Napredni video embed sa shortcode generatorom, preview-om i naprednim opcijama. Autor: Srki Mafia
Version: 1.0.0
Author: Srki Mafia
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'SVE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SVE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

defined( 'SVE_VERSION' ) || define( 'SVE_VERSION', '1.0.0' );

// Includujemo osnovne fajlove
require_once SVE_PLUGIN_DIR . 'includes/class-video-embed-manager.php';
require_once SVE_PLUGIN_DIR . 'shortcodes/video-shortcode.php';
require_once SVE_PLUGIN_DIR . 'includes/class-videojs-manager.php';

// Dodajemo hook za kreiranje custom tabele prilikom aktivacije plugina
register_activation_hook( __FILE__, [ 'SVE_Video_Embed_Manager', 'activate_plugin' ] );

// Admin meni
add_action( 'admin_menu', [ 'SVE_Video_Embed_Manager', 'register_admin_menu' ] );

// Register Video.js submenu after main menu
add_action( 'admin_menu', function() {
    if ( class_exists( 'SVE_VideoJS_Manager' ) ) {
        new SVE_VideoJS_Manager();
    }
}, 11 );

// Shortcode
add_action( 'init', [ 'SVE_Video_Embed_Manager', 'register_shortcode' ] );

// AJAX handlers
add_action( 'wp_ajax_sve_upload_file', function() {
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
        wp_send_json_error(['message' => $result['error']]);
    }
    
    wp_send_json_success(['url' => $result['url']]);
});

// AJAX handler za upis videa u custom tabelu
add_action( 'wp_ajax_sve_save_video', function() {
    check_ajax_referer('sve_upload_file', 'nonce');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error(['message' => 'You are not allowed to perform this action.']);
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'sve_videos';
    $title = sanitize_text_field($_POST['title'] ?? '');
    $shortcode = stripslashes(sanitize_textarea_field($_POST['shortcode'] ?? ''));
    $params = wp_unslash($_POST['params'] ?? '');
    $params_arr = json_decode($params, true);
    $url = isset($params_arr['url']) ? $params_arr['url'] : '';
    $lottie = isset($params_arr['lottie']) ? $params_arr['lottie'] : '';
    if (!$title || !$shortcode || !$url) {
        wp_send_json_error(['message' => 'Missing required fields.']);
    }
    // Check for duplicate by URL and lottie (if present)
    /*
    $where = "params LIKE %s";
    $args = ['%' . $wpdb->esc_like($url) . '%'];
    if ($lottie) {
        $where .= " AND params LIKE %s";
        $args[] = '%' . $wpdb->esc_like($lottie) . '%';
    }
    $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_name WHERE $where LIMIT 1", ...$args));
    if ($existing) {
        wp_send_json_success(['id' => $existing->id]);
    }
    */
    $result = $wpdb->insert($table_name, [
        'title' => $title,
        'shortcode' => $shortcode,
        'params' => $params,
        'created_at' => current_time('mysql', 1)
    ]);
    if ($wpdb->insert_id) {
        wp_send_json_success(['id' => $wpdb->insert_id]);
    } else {
        wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
    }
});

// AJAX handler za ažuriranje shortcode-a sa id-jem
add_action( 'wp_ajax_sve_update_shortcode', function() {
    check_ajax_referer('sve_upload_file', 'nonce');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error(['message' => 'You are not allowed to perform this action.']);
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'sve_videos';
    $id = intval($_POST['id'] ?? 0);
    $shortcode = stripslashes(sanitize_textarea_field($_POST['shortcode'] ?? ''));
    if (!$id || !$shortcode) {
        wp_send_json_error(['message' => 'Missing required fields.']);
    }
    $updated = $wpdb->update($table_name, [ 'shortcode' => $shortcode ], [ 'id' => $id ]);
    if ($updated !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
    }
});

// Enqueue assets
add_action( 'admin_enqueue_scripts', function($hook) {
    if ( $hook === 'toplevel_page_sve-settings' || $hook === 'admin_page_sve-edit-video' ) {
        // Uklonjen video-enhancer.js iz admina
        wp_enqueue_style( 'sve-style', SVE_PLUGIN_URL . 'public/assets/css/style.css', [], SVE_VERSION );
        wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', [], '5.3.3' );
        wp_enqueue_script( 'lottie-web', 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js', [], '5.12.2', true );
        wp_localize_script( 'lottie-web', 'sveUpload', [
            'nonce' => wp_create_nonce('sve_upload_file'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ] );
    }
});
add_action( 'wp_enqueue_scripts', function() {
    if ( is_singular() ) {
        global $post;
        if ( isset($post->post_content) && has_shortcode( $post->post_content, 'smart_video_embed' ) ) {
            wp_enqueue_style( 'sve-style', SVE_PLUGIN_URL . 'public/assets/css/style.css', [], SVE_VERSION );
            wp_enqueue_script( 'lottie-web', 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js', [], '5.12.2', false );
            wp_enqueue_script( 'sve-video-enhancer', SVE_PLUGIN_URL . 'public/assets/js/video-enhancer.js', ['lottie-web'], SVE_VERSION, true );
        }
    }
}); 

// Instantiate Video.js manager (separate from main plugin logic)
if ( class_exists( 'SVE_VideoJS_Manager' ) ) {
    new SVE_VideoJS_Manager();
} 