<?php

namespace Pmld\App\Assets;

use Pimple\Container;
use Pmld\Support\Facades\Asset;
use Pmld\Support\ServiceProvider;

class AssetsAdmin extends ServiceProvider
{
    /** @inheritdoc */
    public function register(Container $app)
    {
        \add_action('admin_enqueue_scripts', [$this, 'scripts']);
        \add_action('admin_enqueue_scripts', [$this, 'styles']);
    }

    /**
     * Registered Scripts;
     *
     * @author rumur
     */
    public function scripts()
    {
        $move_to_footer = true;

        \wp_enqueue_script('pmld.admin-app', Asset::get('js/app.js'), ['pmld.vendor'], null, ! $move_to_footer );
    }

    /**
     * Registered Styles;
     *
     * @author rumur
     */
    public function styles()
    {
         \wp_enqueue_style('pmld.admin-main', Asset::get('css/admin.css'), [], null );
    }
}
