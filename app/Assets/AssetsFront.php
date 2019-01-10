<?php

namespace Pmld\App\Assets;

use Pimple\Container;
use Pmld\Support\Facades\Asset;
use Pmld\Support\ServiceProvider;

class AssetsFront extends ServiceProvider
{
    /** @inheritdoc */
    public function register(Container $app)
    {
        \add_action('wp_enqueue_scripts', [$this, 'scripts']);

        \add_action('wp_enqueue_scripts', [$this, 'styles']);

        /**
         * Add <body> classes.
         * Helps JS to determine on which page is user right now.
         */
        \add_filter('body_class', function (array $classes) {
            /** Add page slug if it doesn't exist */
            if (\is_single() || \is_page() && ! \is_front_page()) {
                if (! in_array(basename(\get_permalink()), $classes)) {
                    $classes[] = basename(\get_permalink());
                }
            }

            return array_filter($classes);
        });

        /**
         * Load the assets with defer.
         */
        \add_filter( 'script_loader_tag', function($tag, $handle) {
            $replace = in_array($handle, [
                'pmld.vendor',
                'pmld.main',
                'pmld.app'
            ]);

            return ! $replace
                ? $tag
                : str_replace(' src', ' defer src', $tag);
        }, 10, 2);
    }

    /**
     * Registered Scripts;
     *
     * @author rumur
     */
    public function scripts()
    {
        $to_footer = true;
        $to_header = false;

        \wp_enqueue_script('pmld.app', Asset::get('js/app.js'), ['pmld.vendor'], null, $to_header );
        \wp_enqueue_script('pmld.main', Asset::get('js/main.js'), ['jquery', 'pmld.vendor'], null, $to_footer );
    }

    /**
     * Registered Styles;
     *
     * @author rumur
     */
    public function styles()
    {
        \wp_enqueue_style('pmld.app', Asset::get('css/app.css'), [], null );
        \wp_enqueue_style('pmld.main', Asset::get('css/main.css'), [], null );
    }
}
