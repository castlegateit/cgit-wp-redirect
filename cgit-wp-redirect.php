<?php

/*

Plugin Name: Castlegate IT WP Redirect
Plugin URI: https://github.com/castlegateit/cgit-wp-redirect
Description: Basic URL redirects
Version: 1.1.0
Author: Castlegate IT
Author URI: https://www.castlegateit.co.uk/
Network: true

Copyright (c) 2019 Castlegate IT. All rights reserved.

*/

if (!defined('ABSPATH')) {
    wp_die('Access denied');
}

define('CGIT_REDIRECT_PLUGIN', __FILE__);

require_once __DIR__ . '/classes/autoload.php';

$plugin = new \Cgit\Redirect\Plugin;

do_action('cgit_redirect_plugin', $plugin);
do_action('cgit_redirect_loaded');
