<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SVE_Video_Embed_Manager {
    public static function register_admin_menu() {
        add_menu_page(
            'Smart Video Embed',
            'Smart Video Embed',
            'manage_options',
            'sve-settings',
            [ __CLASS__, 'render_settings_page' ],
            'dashicons-format-video',
            56
        );
        add_submenu_page(
            'sve-settings',
            'All Videos',
            'All Videos',
            'manage_options',
            'sve-videos',
            [ __CLASS__, 'render_videos_list_page' ]
        );
        // Dodajemo nevidljivu stranicu za edit video
        add_submenu_page(
            null,
            'Edit Video',
            'Edit Video',
            'manage_options',
            'sve-edit-video',
            [ __CLASS__, 'render_edit_video_page' ]
        );
        
        // Enqueue styles for edit page
        add_action('admin_enqueue_scripts', function($hook) {
            if ($hook === 'smart-video-embed_page_sve-edit-video') {
                wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', [], '5.3.3');
                wp_enqueue_style('sve-style', SVE_PLUGIN_URL . 'public/assets/css/style.css', [], SVE_VERSION);
            }
        });
    }

    public static function render_settings_page() {
        require_once SVE_PLUGIN_DIR . 'admin/settings-page.php';
    }

    public static function register_shortcode() {
        // Shortcode registration is now handled in video-shortcode.php
    }

    public static function render_videos_list_page() {
        if ( ! current_user_can('manage_options') ) {
            wp_die(__('You do not have permission to access this page.'));
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'sve_videos';

        // DELETE HANDLER
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete' && isset($_GET['_wpnonce'])) {
            $id = intval($_GET['id']);
            $nonce = $_GET['_wpnonce'];
            if (wp_verify_nonce($nonce, 'sve_delete_video_' . $id)) {
                $deleted = $wpdb->delete($table_name, ['id' => $id]);
                if ($deleted) {
                    // Remove all instances of the shortcode from posts and pages
                    $shortcode_pattern = '\[smart_video_embed id="' . $id . '"[^\]]*\]';
                    $posts = $wpdb->get_results($wpdb->prepare(
                        "SELECT ID, post_content FROM {$wpdb->posts} WHERE (post_type = 'post' OR post_type = 'page') AND post_status IN ('publish', 'draft', 'pending', 'future', 'private') AND post_content REGEXP %s",
                        $shortcode_pattern
                    ));
                    if ($posts) {
                        foreach ($posts as $p) {
                            $new_content = preg_replace('/' . $shortcode_pattern . '/i', '', $p->post_content);
                            if ($new_content !== $p->post_content) {
                                wp_update_post([
                                    'ID' => $p->ID,
                                    'post_content' => $new_content
                                ]);
                            }
                        }
                    }
                    echo '<div class="notice notice-success is-dismissible"><p>Video deleted successfully. All shortcodes removed from posts and pages.</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to delete video. Please try again.</p></div>';
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Invalid nonce. Action not allowed.</p></div>';
            }
        }

        $videos = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        ?>
        <div class="wrap sve-admin-main">
            <h1 class="display-5 fw-bold mb-2" style="letter-spacing:0.01em;">All Videos</h1>
            <div class="text-muted mb-3" style="font-size:1.08rem;max-width:700px;">For automatic updates everywhere, use the <b>shortcode with ID</b> (recommended): <span style="background:#f0f6ff;padding:2px 8px;border-radius:6px;font-family:monospace;">[smart_video_embed id=\"X\"]</span>. When you edit a video, all pages using this shortcode will update automatically.</div>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Video Title</th>
                        <th>Video URL</th>
                        <th>Shortcode (ID, recommended)</th>
                        <th>Full Shortcode</th>
                        <th>Used On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($videos): foreach ($videos as $video): ?>
                    <?php $params = json_decode($video->params, true); ?>
                    <tr>
                        <td><?php echo esc_html($video->title); ?></td>
                        <td><?php echo esc_html($params['url'] ?? ''); ?></td>
                        <td><code>[smart_video_embed id="<?php echo esc_attr($video->id); ?>"]</code></td>
                        <td><code><?php echo esc_html($video->shortcode); ?></code></td>
                        <td>
                            <?php
                            $shortcode_pattern = '[smart_video_embed id="' . $video->id . '"';
                            $posts = $wpdb->get_results($wpdb->prepare(
                                "SELECT ID, post_title, post_type FROM {$wpdb->posts} WHERE post_status = 'publish' AND (post_type = 'post' OR post_type = 'page') AND post_content LIKE %s",
                                '%' . $wpdb->esc_like($shortcode_pattern) . '%'
                            ));
                            if ($posts) {
                                $links = array();
                                foreach ($posts as $p) {
                                    $links[] = '<a href="' . get_permalink($p->ID) . '" target="_blank">' . esc_html($p->post_title) . '</a>';
                                }
                                echo implode('<br>', $links);
                            } else {
                                echo '<span class="text-muted">Not used</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=sve-edit-video&id=' . $video->id); ?>" class="button">Edit</a> 
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=sve-videos&action=delete&id=' . $video->id), 'sve_delete_video_' . $video->id); ?>" class="button delete">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6">No videos found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Helper: Parse shortcode string into associative array
    private static function parse_shortcode_params($shortcode) {
        $params = [];
        if (preg_match_all('/(\w+)="([^"]*)"/', $shortcode, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $params[$m[1]] = $m[2];
            }
        }
        return $params;
    }

    public static function render_edit_video_page() {
        if ( ! current_user_can('manage_options') ) {
            wp_die(__('You do not have permission to access this page.'));
        }
        

        global $wpdb;
        $table_name = $wpdb->prefix . 'sve_videos';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id) {
            echo '<div class="notice notice-error"><p>Invalid video ID.</p></div>';
            return;
        }
        $video = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        if (!$video) {
            echo '<div class="notice notice-error"><p>Video not found.</p></div>';
            return;
        }
        // Check if it's an ID shortcode or full shortcode
        if (strpos($video->shortcode, 'id="') !== false) {
            // It's an ID shortcode, get params from the database
            $params = json_decode($video->params, true);
        } else {
            // It's a full shortcode, parse it
            $params = self::parse_shortcode_params($video->shortcode);
        }
        
        $is_videojs = ($params['type'] === 'videojs' || isset($params['theme']));
        

        

        
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['video_id']) &&
            wp_verify_nonce($_POST['_wpnonce'] ?? '', 'sve_edit_video_' . $id)
        ) {

            
            if ($is_videojs) {
                // Video.js edit logic
                $new_title = sanitize_text_field($_POST['sve_title'] ?? '');
                $new_url = sanitize_text_field($_POST['sve_videojs_url'] ?? '');
                $new_thumbnail = sanitize_text_field($_POST['sve_videojs_thumbnail'] ?? '');
                $new_lottie = sanitize_text_field($_POST['sve_videojs_lottie'] ?? '');
                $new_theme = sanitize_text_field($_POST['sve_videojs_theme'] ?? 'classic');
                $new_skip = !empty($_POST['sve_videojs_skip']) ? '1' : '0';
                $new_lazy = !empty($_POST['sve_videojs_lazy']) ? '1' : '0';
                $new_modal = !empty($_POST['sve_videojs_modal']) ? '1' : '0';
                $new_autoplay = !empty($_POST['sve_videojs_autoplay']) ? '1' : '0';
                $new_mute = !empty($_POST['sve_videojs_mute']) ? '1' : '0';
                $new_aspect_ratio = sanitize_text_field($_POST['sve_videojs_aspect_ratio'] ?? '16:9');
                $new_max_width = sanitize_text_field($_POST['sve_videojs_max_width'] ?? '800px');
                
                if (!$new_url) {
                    echo '<div class="notice notice-error"><p>Video URL is required.</p></div>';
                } else {
                    $new_shortcode = '[smart_video_embed type="videojs" url="' . $new_url . '"';
                    if ($new_thumbnail) $new_shortcode .= ' thumbnail="' . $new_thumbnail . '"';
                    if ($new_lottie) {
                        $new_shortcode .= ' lottie="' . $new_lottie . '"';
                        if ($new_skip === '1') $new_shortcode .= ' skip_button="1"';
                    }
                    if ($new_theme && $new_theme !== 'classic') $new_shortcode .= ' theme="' . $new_theme . '"';
                    if ($new_lazy === '1') $new_shortcode .= ' lazy_load="1"';
                    if ($new_modal === '1') $new_shortcode .= ' modal="1"';
                    if ($new_autoplay === '1') $new_shortcode .= ' autoplay="1"';
                    if ($new_mute === '1') $new_shortcode .= ' mute="1"';
                    if ($new_aspect_ratio) $new_shortcode .= ' aspect_ratio="' . $new_aspect_ratio . '"';
                    if ($new_max_width) $new_shortcode .= ' max_width="' . $new_max_width . '"';
                    $new_shortcode .= ']';
                    

                    
                    $updated = $wpdb->update(
                        $table_name,
                        [
                            'title' => $new_title,
                            'shortcode' => stripslashes($new_shortcode),
                            'params' => json_encode([
                                'url' => $new_url,
                                'thumbnail' => $new_thumbnail,
                                'lottie' => $new_lottie,
                                'theme' => $new_theme,
                                'skip_button' => $new_skip,
                                'lazy_load' => $new_lazy,
                                'modal' => $new_modal,
                                'autoplay' => $new_autoplay,
                                'mute' => $new_mute,
                                'aspect_ratio' => $new_aspect_ratio,
                                'max_width' => $new_max_width
                            ])
                        ],
                        [ 'id' => $id ]
                    );
                    

                    
                    if ($updated !== false) {
                        echo '<div class="notice notice-success is-dismissible"><p>Video.js video updated successfully.</p></div>';
                        echo '<script>setTimeout(function(){ window.location = "' . admin_url('admin.php?page=sve-videos') . '"; }, 1200);</script>';
                    } else {
                        echo '<div class="notice notice-error"><p>Database error. Please try again.</p></div>';
                    }
                }
            } else {
                // Classic video edit logic
                $new_title = sanitize_text_field($_POST['sve_title'] ?? '');
                $new_url = sanitize_text_field($_POST['sve_url'] ?? '');
                $new_thumbnail = sanitize_text_field($_POST['sve_thumbnail_url'] ?? '');
                $new_lottie = sanitize_text_field($_POST['sve_lottie_url'] ?? '');
                $new_skip = !empty($_POST['sve_lottie_skip']) ? '1' : '0';
                $new_lazy = !empty($_POST['sve_lazy_load']) ? '1' : '0';
                $new_modal = !empty($_POST['sve_modal']) ? '1' : '0';
                $new_autoplay = !empty($_POST['sve_autoplay']) ? '1' : '0';
                $new_mute = !empty($_POST['sve_mute']) ? '1' : '0';
                $new_aspect_ratio = sanitize_text_field($_POST['sve_aspect_ratio'] ?? '16:9');
                $new_max_width = sanitize_text_field($_POST['sve_max_width'] ?? '800px');
                if (!$new_url) {
                    echo '<div class="notice notice-error"><p>Video URL is required.</p></div>';
                } else {
                    $new_shortcode = '[smart_video_embed url="' . $new_url . '"';
                    if ($new_thumbnail) $new_shortcode .= ' thumbnail="' . $new_thumbnail . '"';
                    if ($new_lottie) {
                        $new_shortcode .= ' lottie="' . $new_lottie . '"';
                        if ($new_skip === '1') $new_shortcode .= ' skip_button="1"';
                    }
                    if ($new_lazy === '1') $new_shortcode .= ' lazy_load="1"';
                    if ($new_modal === '1') $new_shortcode .= ' modal="1"';
                    if ($new_autoplay === '1') $new_shortcode .= ' autoplay="1"';
                    if ($new_mute === '1') $new_shortcode .= ' mute="1"';
                    if ($new_aspect_ratio) $new_shortcode .= ' aspect_ratio="' . $new_aspect_ratio . '"';
                    if ($new_max_width) $new_shortcode .= ' max_width="' . $new_max_width . '"';
                    $new_shortcode .= ']';
                    $updated = $wpdb->update(
                        $table_name,
                        [
                            'title' => $new_title,
                            'shortcode' => stripslashes($new_shortcode),
                            'params' => json_encode([
                                'url' => $new_url,
                                'thumbnail' => $new_thumbnail,
                                'lottie' => $new_lottie,
                                'skip_button' => $new_skip,
                                'lazy_load' => $new_lazy,
                                'modal' => $new_modal,
                                'autoplay' => $new_autoplay,
                                'mute' => $new_mute,
                                'aspect_ratio' => $new_aspect_ratio,
                                'max_width' => $new_max_width
                            ])
                        ],
                        [ 'id' => $id ]
                    );
                    if ($updated !== false) {
                        echo '<div class="notice notice-success is-dismissible"><p>Video updated successfully.</p></div>';
                        echo '<script>setTimeout(function(){ window.location = "' . admin_url('admin.php?page=sve-videos') . '"; }, 1200);</script>';
                    } else {
                        echo '<div class="notice notice-error"><p>Database error. Please try again.</p></div>';
                    }
                }
            }
        }
        echo '<div class="wrap sve-admin-main">';
        if ($is_videojs) {
            echo '<h1 class="display-5 fw-bold mb-2" style="letter-spacing:0.01em;">Edit Video.js Video</h1>';
            echo '<div class="text-muted mb-4" style="font-size:1.08rem;max-width:700px;">Update your Video.js video settings, theme, thumbnail, Lottie animation, and advanced options. When finished, click Save Video to apply changes.</div>';
            // Include Video.js form
            include SVE_PLUGIN_DIR . 'admin/videojs-edit-form.php';
        } else {
            echo '<h1 class="display-5 fw-bold mb-2" style="letter-spacing:0.01em;">Edit Video</h1>';
            echo '<div class="text-muted mb-4" style="font-size:1.08rem;max-width:700px;">Update your video settings, thumbnail, Lottie animation, and advanced options. When finished, click Save Video to apply changes.</div>';
            $form_values = [
                'title' => $video->title ?? '',
                'url' => $params['url'] ?? '',
                'thumbnail' => $params['thumbnail'] ?? '',
                'lottie' => $params['lottie'] ?? '',
                'skip_button' => ($params['skip_button'] ?? '') == '1',
                'lazy_load' => ($params['lazy_load'] ?? '') == '1',
                'modal' => ($params['modal'] ?? '') == '1',
                'autoplay' => ($params['autoplay'] ?? '') == '1',
                'mute' => ($params['mute'] ?? '') == '1',
                'aspect_ratio' => $params['aspect_ratio'] ?? '16:9',
                'max_width' => $params['max_width'] ?? '800px',
                'nonce_action' => 'sve_edit_video_' . $id,
                'video_id' => $id,
                'cancel_url' => admin_url('admin.php?page=sve-videos'),
                'mode' => 'edit',
            ];
            include SVE_PLUGIN_DIR . 'admin/video-form.php';
        }
        echo '</div>';
    }

    // Kreira custom tabelu za video zapise prilikom aktivacije plugina
    public static function activate_plugin() {
        // TEMP LOG: Provera da li se funkcija poziva
        error_log('SVE activate_plugin called');
        global $wpdb;
        $table_name = $wpdb->prefix . 'sve_videos';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            shortcode VARCHAR(255) NOT NULL,
            params LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public static function enqueue_frontend_scripts() {
        if (!is_admin()) {
            wp_enqueue_style(
                'sve-style',
                SVE_PLUGIN_URL . 'public/assets/css/style.css',
                array(),
                SVE_VERSION
            );
            wp_enqueue_script(
                'lottie-web',
                'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js',
                [],
                '5.12.2',
                false
            );
            wp_enqueue_script(
                'sve-video-enhancer',
                SVE_PLUGIN_URL . 'public/assets/js/video-enhancer.js',
                ['lottie-web'],
                SVE_VERSION,
                true
            );
            // Video.js i pluginovi za frontend
            wp_enqueue_style('videojs', 'https://cdn.jsdelivr.net/npm/video.js@8.10.0/dist/video-js.min.css', [], '8.10.0');
            wp_enqueue_script('videojs', 'https://cdn.jsdelivr.net/npm/video.js@8.10.0/dist/video.min.js', [], '8.10.0', true);
            wp_enqueue_script('videojs-youtube', 'https://cdn.jsdelivr.net/npm/videojs-youtube@3.0.1/dist/Youtube.min.js', ['videojs'], '3.0.1', true);
            wp_enqueue_script('videojs-vimeo', 'https://cdn.jsdelivr.net/npm/videojs-vimeo@2.0.2/dist/videojs-vimeo.min.js', ['videojs'], '2.0.2', true);
            // Video.js teme (uvek učitaj sve, enable samo izabranu)
            wp_enqueue_style('vjs-theme-city', 'https://unpkg.com/@videojs/themes@1.0.1/dist/city/index.css', [], '1.0.1');
            wp_enqueue_style('vjs-theme-fantasy', 'https://unpkg.com/@videojs/themes@1.0.1/dist/fantasy/index.css', [], '1.0.1');
            wp_enqueue_style('vjs-theme-forest', 'https://unpkg.com/@videojs/themes@1.0.1/dist/forest/index.css', [], '1.0.1');
            wp_enqueue_style('vjs-theme-sea', 'https://unpkg.com/@videojs/themes@1.0.1/dist/sea/index.css', [], '1.0.1');
            wp_enqueue_style('vjs-theme-classic', 'https://unpkg.com/@videojs/themes@1.0.1/dist/classic/index.css', [], '1.0.1');
        }
    }
}
add_action('wp_enqueue_scripts', ['SVE_Video_Embed_Manager', 'enqueue_frontend_scripts']); 