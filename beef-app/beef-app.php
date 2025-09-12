<?php
/*
Plugin Name: Beef App
Description: A plugin to integrate a React Beef App into WordPress.
Version: 0.1.7
Author: Beef Reproduction Task Force
*/

function beef_app_enqueue_scripts() {
  $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  wp_register_script('beef-app-config', '', [], null, false);
  wp_enqueue_script('beef-app-config');
  wp_add_inline_script('beef-app-config', "window.BEEF_APP_BASENAME = '{$current_path}';");
  wp_add_inline_script('beef-app-config', "window.BEEF_APP_SHOW_HEADER = false;");
  wp_add_inline_script('beef-app-config', "window.BEEF_APP_SHOW_FOOTER = false;");
  wp_add_inline_script('beef-app-config', "window.BEEF_APP_SHOW_NAVBAR = false;");

  wp_enqueue_style(
      'beef-app-style',
      'https://beef-repro-task-force.github.io/Estrus-Synchronization-Planner-Staging/static/css/main.e800a36e.css'
  );

  // React bundle
  wp_enqueue_script(
      'beef-app-script',
      'https://beef-repro-task-force.github.io/Estrus-Synchronization-Planner-Staging/static/js/main.522f1535.js',
      ['beef-app-config'],
      null,
      true
  );

  // Render after DOM ready
  wp_add_inline_script(
      'beef-app-script',
      "document.addEventListener('DOMContentLoaded', function() {
          if (document.getElementById('root')) {
              window.renderBeefApp();
          } else {
              console.error('Beef App root not found in DOM!');
          }
      });"
  );

  // Capture errors
  wp_add_inline_script(
      'beef-app-script',
      "window.onerror = function(msg, url, line, col, error) {
          console.error('React error:', msg, 'at', url+':'+line+':'+col, error);
      };"
  );

}
add_action('wp_enqueue_scripts', 'beef_app_enqueue_scripts');


function beef_app_shortcode() {
  return '<div id="root"></div>';
}
add_shortcode('beef_app', 'beef_app_shortcode');