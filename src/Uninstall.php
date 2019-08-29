<?php

namespace Rumur\Pimpled\Foundation;

class Uninstall
{
    /**
     * Start the plugin delete process.
     *
     * @uses do_action()
     */
	public static function run()
	{
		if (! defined('WP_UNINSTALL_PLUGIN')) {
			exit;
		}

        $uninstall = new static();

        \do_action('pmld.plugin_uninstall', $uninstall);
	}
}
