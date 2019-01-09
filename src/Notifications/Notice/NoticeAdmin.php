<?php
namespace Pmld\Notifications\Notice;

use \WP_Error;

class NoticeAdmin extends Notice
{
	/**
	 * Render the HTML of messages
	 *
	 * @param array $messages
	 * @param string $type
	 *
	 *
	 */
    protected function render(array $messages = [], $type = 'error')
	{
		$notice_class = ['notice'];

		array_push( $notice_class, 'notice-' . $type );

		$html = $this->htmlTemplate();

        array_walk($messages, function ($message) use ($notice_class, $html) {
			$class = $notice_class;

			if ( $message['dismissible'] ) {
				array_push( $class, 'is-dismissible' );
			}

			printf( $html, join( ' ', $class ), $message['message'] );
		});
	}

    /**
     * Provides the template for notice.
     *
     * @return string
     *
     * @author rumur
     */
	protected function htmlTemplate()
    {
        return \apply_filters('pmld.admin_notice_template', '<div class="%1$s"><p><strong>%2$s</strong></p></div>');
    }

	/**
	 * Add message to the global notifications
	 *
	 * @param string $message       Message Content
	 * @param string $type          Could be error | warning | update | success
	 * @param bool $dismissible
	 *
	 *
	 *
	 * @return bool
	 */
	public function add($message, $type = 'error', $dismissible = false )
	{
		if ( $message ) {

            if (! $this->isTypeValid($type)) {
                $type = 'error';
            }

			$key = md5( $message );
			$this->storage[ $type ][ $key ]['message'] = $message;
			$this->storage[ $type ][ $key ]['dismissible'] = $dismissible;

			return true;
		}

		return false;
	}

	/**
	 * Add an Info messages to the global notifications
	 *
	 * @param string $message
	 * @param bool $dismissible
	 *
	 *
	 *
	 * @return bool
	 */
	public function info($message, $dismissible = false )
	{
		return $this->add( $message, 'info', $dismissible );
	}

	/**
	 * Add an Error messages to the global notifications
	 *
	 * @param string|\WP_Error $message
	 * @param bool $dismissible
	 *
	 *
	 *
	 * @return bool
	 */
	public function error($message, $dismissible = false )
	{
        return function_exists('is_wp_error') && is_wp_error($message)
            ? $this->wpError($message, $dismissible)
            : $this->add($message, 'error', $dismissible);
	}

	/**
	 * Add Warning messages to the global notifications
	 *
	 * @param string $message
	 * @param bool $dismissible
	 *
	 *
	 *
	 * @return bool
	 */
	public function warning($message, $dismissible = false )
	{
		return $this->add( $message, 'warning', $dismissible );
	}

	/**
	 * Add Success messages to the global notifications
	 *
	 * @param string $message
	 * @param bool $dismissible
	 *
	 *
	 *
	 * @return bool
	 */
	public function success($message, $dismissible = false )
	{
		return $this->add( $message, 'success', $dismissible );
	}

	/**
	 * Add a WP_Error messages to the global notifications
	 *
	 * @param \WP_Error $errors
	 * @param bool $dismissible
	 *
	 *
	 *
	 * @return bool
	 */
    public function wpError(WP_Error $errors, $dismissible = false)
	{
		if ( $errors->get_error_code() ) {

			foreach ( $errors->errors as $code => $message ) {

				$this->error( $errors->get_error_message( $code ), $dismissible );

				// Clearing the WP_Error object
				$errors->remove( $code );
			}

			return true;
		}

		return false;
	}
}
