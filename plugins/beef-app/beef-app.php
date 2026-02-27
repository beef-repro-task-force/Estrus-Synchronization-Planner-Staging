<?php
/*
Plugin Name: Beef App
Description: A plugin to integrate a React Beef App into WordPress.
Version: 0.5.0
Author: Beef Reproduction Task Force
*/

function beef_app_enqueue_scripts() {
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Config script
    wp_register_script('beef-app-config', '', [], null, false);
    wp_enqueue_script('beef-app-config');
    wp_add_inline_script('beef-app-config', "window.BEEF_APP_BASENAME = '{$current_path}';");
    wp_add_inline_script('beef-app-config', "window.BEEF_APP_SHOW_HEADER = false;");
    wp_add_inline_script('beef-app-config', "window.BEEF_APP_SHOW_FOOTER = false;");
    wp_add_inline_script('beef-app-config', "window.BEEF_APP_SHOW_NAVBAR = false;");
    wp_add_inline_script('beef-app-config', "window.BEEF_APP_SHOW_PAGE_TITLE = false;");

    // Manifest
    $manifest_url = 'https://beef-repro-task-force.github.io/Estrus-Synchronization-Planner-Staging/asset-manifest.json';
    $response = wp_remote_get($manifest_url);
    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $manifest = json_decode($body, true);

        if (isset($manifest['files']['main.css'])) {
            wp_enqueue_style(
                'beef-app-style',
                'https://beef-repro-task-force.github.io' . $manifest['files']['main.css']
            );
        }

        if (isset($manifest['files']['main.js'])) {
            wp_enqueue_script(
                'beef-app-script',
                'https://beef-repro-task-force.github.io' . $manifest['files']['main.js'],
                ['beef-app-config'],
                null,
                true
            );

            // Mount app
            wp_add_inline_script(
                'beef-app-script',
                "function mountBeefApp() {
                    if (document.getElementById('root') && typeof window.renderBeefApp === 'function') {
                        window.renderBeefApp();
                    }
                }
                document.addEventListener('DOMContentLoaded', mountBeefApp);"
            );

            // Capture errors
            wp_add_inline_script(
                'beef-app-script',
                "window.onerror = function(msg, url, line, col, error) {
                    console.error('React error:', msg, 'at', url+':'+line+':'+col, error);
                };"
            );
        }
    }
}

// Shortcode
function beef_app_shortcode() {
    // Enqueue scripts *only when shortcode is used*
    beef_app_enqueue_scripts();

    return '<div id="root"></div>
    <script>
        if (typeof mountBeefApp === \"function\") mountBeefApp();
    </script>';
}
add_shortcode('beef_app', 'beef_app_shortcode');
