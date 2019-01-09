<?php
/**
 * Plugin Name:         Pimpled WP
 * Description:         WordPress Starter plugin + DI Pimple
 * Author:              Rumur
 * Text Domain:         rumur
 * Domain Path:         /lang/
 * Version:             1.0.0
 * Requires at least:   4.4.0
 * Requires PHP:        5.9
 * License:             MIT
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/
if (! class_exists('Pmld\\Plugin')) {
    require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

defined( 'PMLD_TD' ) || define( 'PMLD_TD', 'pmld' );

do_action('pmld.plugin_start', $app = Pmld\Plugin::start(__FILE__));
