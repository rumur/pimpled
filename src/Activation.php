<?php

namespace Pmld\Foundation;

class Activation
{
	/**
	 * Start the plugin activation
     *
     * @uses do_action()
	 */
	public static function start()
	{
	    $activation = new static();

        \do_action('pmld.plugin_activation', $activation);
	}
}
