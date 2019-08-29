<?php

namespace Rumur\Pimpled\Foundation;

class Activation
{
	/**
	 * Start the plugin activation
     *
     * @uses do_action()
	 */
	public static function run()
	{
	    $activation = new static();

        \do_action('pmld.plugin_activation', $activation);
	}
}
