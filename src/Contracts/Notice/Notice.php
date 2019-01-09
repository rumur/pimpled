<?php
namespace Pmld\Contracts\Notice;

interface Notice
{

	/**
	 * Run the Instance with WP hooks
	 *
     * @param array $storage
	 *
	 * @return Notice
	 */
	public static function make(array $storage);

	/**
	 * Add message to the notifications
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @return bool
	 */
    public function add($message, $type = 'error');

	/**
	 * Get all messages
	 * Get messages by type
	 *
	 * @param string $type
	 *
	 * @return array|mixed
	 */
    public function get($type = 'all');

	/**
	 * Remove all messages from global notifications
	 * Remove messages by Group
	 * Remove single message by Group and Key
	 *
	 * @param string $group,
	 * @param null   $key
	 *
	 * @return void
	 */
    public function clear($group = 'all', $key = null);

	/**
	 * Show all messages
	 *
	 * @since 0.0.1
	 */
	public function show();
}
