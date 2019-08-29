<?php
/**
 * Plugin Name:         Pimpled
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

if (! class_exists('Pmld\\App\\Plugin')) {
    require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

defined( 'PMLD_TD' ) || define( 'PMLD_TD', 'pmld' );

do_action('pmld.plugin.start', $app = Pmld\App\Plugin::start(__FILE__));
