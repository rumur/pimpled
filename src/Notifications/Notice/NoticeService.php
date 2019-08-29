<?php

namespace Rumur\Pimpled\Notifications\Notice;

use Pimple\Container;
use Rumur\Pimpled\Support\ServiceProvider;

class NoticeService extends ServiceProvider
{
    /**
     * Load the instance.
     *
     * @author rumur
     */
	function boot()
	{
		if (! session_id()) {
			session_start();
		}
	}

	/**
     * @param Container $app
     *
     * @uses add_action()
	 */
	public function register(Container $app)
	{
        $app['notice.admin'] = function ($app) {

            $storage = $this->getStorageFor('notice.admin');

            $notice = NoticeAdmin::make($storage);

            \add_action('admin_notices', [$notice, 'show']);

            return $notice;
        };

        $app['notice.front'] = function ($app) {

            $storage = $this->getStorageFor('notice.front');

            $notice = NoticeFront::make($storage);

            \add_action('wp_footer', [$notice, 'show']);

            return $notice;
        };
	}

    /**
     * Gets the notifications storage.
     *
     * @param $key
     * @return array
     *
     * @author rumur
     */
	protected function getStorageFor($key)
    {
        return isset($_SESSION[ $key ]) && is_array($_SESSION[ $key ])
            ? $_SESSION[ $key ]
            : [];
    }
}
