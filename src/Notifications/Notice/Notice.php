<?php
namespace Pmld\Notifications\Notice;

use WP_Error;
use Pmld\Contracts\Notice\Notice as NoticeContact;

abstract class Notice implements NoticeContact
{
	/**
	 * List of notifications
	 *
	 * @var array
	 *
	 * @since 0.0.1
	 */
	protected $storage = [];

    /**
     * Notice constructor.
     *
     * @param array $storage
     */
	public function __construct(array &$storage)
	{
        $this->storage = $storage;
	}

    /**
     * Run the Instance with WP hooks
     *
     * @param array $storage
     *
     * @return NoticeAdmin|NoticeContact
     *
     * @author rumur
     */
    public static function make(array $storage)
    {
        $self = new static($storage);

        return $self;
    }

	/**
	 * Render the HTML of messages
	 *
	 * @param array $messages
	 * @param string $type
	 *
	 * @since 0.0.1
	 */
	abstract protected function render(array $messages = array() , $type = 'error');

	/**
	 * Show all messages
	 *
	 * @since 0.0.1
	 */
	public function show()
	{
		$messages = $this->get();

		foreach ( (array) $messages as $code => $_messages ) {

		    if ( empty( $_messages ) ) {
				continue;
			}

			$this->render( $_messages, $code );

			// Clear rendered messages
			$this->clearGroup( $code );
		}
	}

	/**
	 * Remove all messages from global notifications
	 * Remove messages by Group
	 * Remove single message by Group and Key
	 *
	 * @param string $type,
	 * @param null   $key
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
    public function clear($type = 'all', $key = null)
	{
        if ('all' == $type) {
            array_map([$this, 'clearGroup'], array_keys($this->storage));
        } elseif ($this->isTypeValid($type) && array_key_exists($type, $this->storage)) {
            if (! is_null($key)) {
                $this->clearOne($key, $type);
			} else {
                $this->clearGroup($type);
			}
		}
	}

	/**
	 * Remove group of messages from the global notifications
	 *
	 * @param string $group
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
    protected function clearGroup($group)
	{
		$this->storage[ $group ] = [];
	}

	/**
	 * Remove single message from the global notifications by Key and Group
	 *
	 * @param $key
	 * @param $group
	 *
	 * @since 0.0.1
	 */
    protected function clearOne($key, $group)
	{
		if (isset($this->storage[ $group ][ $key ])) {
			unset($this->storage[ $group ][ $key ]);
		}
	}

	/**
	 * Add message to the global notifications
	 *
	 * @param string $message      Message Content
	 * @param string $type         Could be error | warning | update | success
	 *
	 * @since 0.0.1
	 *
	 * @return bool
	 */
    public function add($message, $type = 'error')
	{
		if ( $message ) {

            if (! $this->isTypeValid($type)) {
				$type = 'error';
			}

			$key = md5( $message );
			$this->storage[ $type ][ $key ] = $message;

			return true;
		}

		return false;
	}

	/**
	 * Get all messages
	 * Get messages by type
	 *
	 * @param string $type
	 *
	 * @since 0.0.1
	 *
	 * @return array|mixed
	 */
	public function get($type = 'all')
	{
	    return $this->isTypeValid($type) ? $this->storage[ $type ] : $this->storage;
	}

	/**
	 * Add Info messages to the global notifications
	 *
	 * @param $message
	 *
	 * @since 0.0.1
	 *
     * @return $this
	 */
    public function info($message)
	{
        $this->add($message, 'info');

        return $this;
	}

	/**
	 * Add Error messages to the global notifications
	 *
	 * @since 0.0.1
	 *
	 * @param string|WP_Error $message
     *
     * @return $this
	 */
    public function error($message)
	{
        is_wp_error($message)
            ? $this->wpError($message)
            : $this->add($message, 'error');

        return $this;
	}

	/**
	 * Add Warning messages to the global notifications
	 *
	 * @since 0.0.1
	 *
	 * @param string $message
     *
     * @return $this
	 */
    public function warning($message)
	{
        $this->add($message, 'warning');

        return $this;
	}

	/**
	 * Add Success messages to the global notifications
	 *
	 * @since 0.0.1
	 *
	 * @param $message
     *
     * @return $this
	 */
    public function success($message)
	{
        $this->add($message, 'success');

        return $this;
	}

	/**
	 * Adding error from the WP_Error object
	 *
	 * @param \WP_Error $errors
	 *
	 * @since 0.0.1
     *
     * @return $this
	 */
    public function wpError(WP_Error $errors)
	{
		if ( $errors->get_error_code() ) {
			foreach ( $errors->errors as $code => $message ) {

				$this->error( $errors->get_error_message( $code ) );

				// Clearing the WP_Error object
                $errors->remove($code);
			}
		}

        return $this;
	}

	/**
	 * Get Info messages
	 *
	 * @since 0.0.1
	 *
	 * @return array|mixed
	 */
	public function getInfo()
	{
        return $this->get('info');
	}

	/**
	 * Get Error messages
	 *
	 * @since 0.0.1
	 *
	 * @return array|mixed
	 */
	public function getErrors()
	{
        return $this->get('error');
	}

	/**
	 * Get Warning messages
	 *
	 * @since 0.0.1
	 *
	 * @return array|mixed
	 */
	public function getWarnings()
	{
        return $this->get('warning');
	}

	/**
	 * Get Success messages
	 *
	 * @since 0.0.1
	 *
	 * @return array|mixed
	 */
	public function getSuccess()
	{
        return $this->get('success');
	}

	/**
	 * Check whether Notifications Instance has Errors
	 *
	 * @since 0.0.1
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
        return $this->has('error');
	}

	/**
	 * Check whether Notifications Instance has Warnings
	 *
	 * @since 0.0.1
	 *
	 * @return bool
	 */
	public function hasWarnings()
	{
        return $this->has('warning');
	}

	/**
	 * Check whether Notifications Instance has Success items
	 *
	 * @since 0.0.1
	 *
	 * @return bool
	 */
	public function hasSuccess()
	{
        return $this->has('success');
	}

	/**
	 * Whether Notifications has some items or not.
	 *
	 * @param string $type  type of the Items group
	 *
	 * @since 0.0.1
	 *
	 * @return bool
	 */
    public function has($type = 'all')
	{
        return ! empty($this->get($type));
	}

	/**
	 * The Method serve for throw errors.
	 * Moved to one place.
	 *
	 * @param mixed  $thing
	 * @param string $message
	 *
	 * @since 0.0.1
	 *
	 * @throws NoticeException
	 */
    protected function ensure($thing, $message)
	{
        if (! $thing) {
            throw new NoticeException($message);
		}
	}

    /**
     * Gets available notifications type
     *
     * @return array
     *
     * @author rumur
     */
	protected function getAvailableTypes()
    {
        return [
            'info'   , // blue
            'error'  , // red
            'warning', // yellow/orange
            'success', // green
        ];
    }

    /**
     * Checks whether the type is valid.
     *
     * @param $type
     * @return bool
     *
     * @author rumur
     */
    protected function isTypeValid($type)
    {
        return in_array($type, $this->getAvailableTypes(), true);
    }
}
