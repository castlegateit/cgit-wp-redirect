<?php

/**
 * Plugin Name:  Castlegate IT WP Redirect
 * Plugin URI:   https://github.com/castlegateit/cgit-wp-redirect
 * Description:  Basic URL redirects.
 * Version:      1.2.1
 * Requires PHP: 8.2
 * Author:       Castlegate IT
 * Author URI:   https://www.castlegateit.co.uk/
 * License:      MIT
 * Update URI:   https://github.com/castlegateit/cgit-wp-redirect
 */

use Castlegate\Redirect\Plugin;

if (!defined('ABSPATH')) {
    wp_die('Access denied');
}

define('CGIT_WP_REDIRECT_VERSION', '1.2.1');
define('CGIT_WP_REDIRECT_PLUGIN_FILE', __FILE__);
define('CGIT_WP_REDIRECT_PLUGIN_DIR', __DIR__);

require_once __DIR__ . '/vendor/autoload.php';

Plugin::init();
