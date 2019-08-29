<?php

namespace Rumur\Pimpled\Support\Facades;

/**
 * Class NoticeFront.
 *
 * @method static add($message, $type = 'error', $dismissible = false )
 * @method static info($message, $dismissible = false )
 * @method static error($message, $dismissible = false )
 * @method static wpError(\WP_Error $errors, $dismissible = false )
 * @method static warning($message, $dismissible = false )
 * @method static success($message, $dismissible = false )
 * @method static clear( $group = 'all', $key = null )
 * @method static get( $type = 'all' )
 * @method static getInfo()
 * @method static getErrors()
 * @method static getWarnings()
 * @method static getSuccess()
 * @method static hasErrors()
 * @method static hasNotices()
 * @method static hasWarnings()
 * @method static hasSuccess()
 *
 *
 * @see \Rumur\Pimpled\Notifications\Notice\NoticeFront
 */
class NoticeFront extends Facade
{
	/**
	 * Return the igniter service key responsible for the Notice class.
	 * The key must be the same as the one used in the assigned
	 * igniter service.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'notice.front';
	}
}
