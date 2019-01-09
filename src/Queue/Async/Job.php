<?php

namespace Pmld\Queue;

use Pmld\Contracts\Queue\Job as QueueJobContract;

abstract class Job implements QueueJobContract
{
    /** @var string */
    protected $pointer;

    /** @var string Query url */
    protected $query_url;

    /** @var array */
    protected $query_args;

    /** @var array */
    protected $post_args;

    /** @var array Payload */
    protected $payload = [];

    /** @var string Prefix */
    protected $prefix = 'pmld';

    /** @var string Action */
    protected $action = 'async_job';

    /**
     * AsyncJob constructor.
     */
    public function __construct()
    {
        $this->pointer = "{$this->prefix}_{$this->action}";
    }

    /**
     * Handle
     *
     * Override this method to perform any actions required
     * during the async request.
     */
    abstract protected function handle();

    /**
     * Factory.
     *
     * @return $this
     *
     * @author rumur
     */
    public static function make()
    {
        $self = new static();

        $self->register();

        return $self;
    }

    /**
     * Register actions.
     *
     * @return $this
     */
    public function register()
    {
        \add_action( 'rest_api_init', function () {
            \register_rest_route('async-job/v1', $this->pointer, [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'maybeHandle'],
            ]);
        });

        return $this;
    }

    /** {@inheritdoc} */
    public function set(array $payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /** @return string */
    protected function getQueryUrl()
    {
        return ! is_null($this->query_url)
            ? $this->query_url
            : \rest_url( 'async-job/v1/'. $this->pointer );
    }

    /** @return array */
    protected function getQueryArgs()
    {
        return ! is_null($this->query_args)
            ? $this->query_args
            : [
                'token' => \wp_hash_password( $this->pointer ),
              ];
    }

    /** @return array */
    protected function getPostArgs()
    {
        return ! is_null($this->post_args)
            ? $this->post_args
            : [
                'timeout'   => 0.01,
                'blocking'  => false,
                'body'      => $this->payload,
                'cookies'   => $_COOKIE,
                'sslverify' => \apply_filters( 'https_local_ssl_verify', false ),
            ];
    }

    /**
     * Dispatch the async request
     *
     * @return array|\WP_Error
     */
    public function dispatch()
    {
        $args = $this->getQueryArgs();
        $url = \add_query_arg($this->getQueryArgs(), $this->getQueryUrl());

        return \wp_remote_post(esc_url_raw($url), $args);
    }

    /**
     * Check whether we can handle this process or not.
     * If so calls the `handle` method.
     */
    public function maybeHandle()
    {
        // Don't lock up other requests while processing
        session_write_close();

        if (! \wp_check_password($this->pointer, $_REQUEST['token'])) {
            \wp_die('-1');
        }

        $this->handle();

        \wp_die('Done');
    }
}
