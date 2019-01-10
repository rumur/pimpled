<?php

namespace Pmld\App\Assets;

use Pimple\Container;
use Pmld\Support\Facades\Asset;
use Pmld\Support\ServiceProvider;

class AssetsCommon extends ServiceProvider {

    /** @inheritdoc */
    public function register(Container $app)
    {
        \add_action('wp_enqueue_scripts', [$this, 'registerScripts']);
        \add_action('admin_enqueue_scripts', [$this, 'registerScripts']);
    }

    public function registerScripts()
    {
        \wp_register_script('pmld.manifest', Asset::get('js/manifest.js'), [], null, true );
        \wp_register_script('pmld.vendor', Asset::get('js/vendor.js'), ['pmld.manifest'], null, true );
    }
}
