<?php

namespace Rumur\Pimpled\Database;

use Pimple\Container;
use Rumur\Pimpled\Support\ServiceProvider;
use Rumur\Pimpled\Contracts\Database\Migration as MigrationContract;

abstract class Migration extends ServiceProvider implements MigrationContract
{
    /** @var Container */
    protected $app;

    /** @var \wpdb */
    protected $db;

    /**
     * @inheritdoc
     *
     * @uses \add_action()
     */
    public function register(Container $app)
    {
        $this->app = $app;

        /** Run migration in forced mode */
        \add_action('pmld.database.migration', function() {
            $this->loadDependenciesWith('force');
        });

        /** Run migration when plugin is activating */
        \add_action('pmld.database.activation', function() {
            $this->loadDependenciesWith('up');
        });

        /** Run migration when plugin is uninstalling */
        \add_action('pmld.database.uninstall', function() {
            $this->loadDependenciesWith('down');
        });
    }

    /**
     * load dependencies and run the action.
     *
     * @param $action
     */
    public function loadDependenciesWith($action)
    {
        if (is_callable([$this, $action])) {

            global $wpdb;

            $this->db = $wpdb;

            /** Load helpers */
            if (! function_exists('dbDelta')) {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            }

            $this->$action();
        }
    }
}
