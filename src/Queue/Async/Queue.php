<?php

namespace Rumur\Pimpled\Queue;

use Rumur\Pimpled\Contracts\Queue\Queue as QueueContract;

abstract class Queue extends Job implements QueueContract
{
    /** @var bool Status for Done task. */
    const TASK_DONE = false;

    /** @var string Store key in DB */
    protected $store_key;

    /** @var string Action */
    protected $action = 'async_queue';

    /** @var int Start time of current process. */
    protected $start_time = 0;

    /** @var string */
    protected $cron_hook_identifier;

    /** @var int Cron Health Check Interval in Minutes */
    protected $cron_healthcheck_interval = 5;

    /** @var string */
    protected $cron_interval_identifier;

    /** @var int  */
    protected $queue_lock_time = MINUTE_IN_SECONDS;

    /**
     * AsyncQueue constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->cron_hook_identifier = "{$this->pointer}_cron";
        $this->cron_interval_identifier = "{$this->pointer}_cron_interval";
    }

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over.
     *
     * @return mixed
     */
    abstract protected function task( $item );

    /** {@inheritdoc} */
    public function register()
    {
        parent::register();

        \add_filter('cron_schedules', [$this, 'scheduleCronHealthCheck']);
        \add_action($this->cron_hook_identifier, [$this, 'handleCronHealthCheck']);

        return $this;
    }

    /**
     * Dispatch
     *
     * @return array|\WP_Error
     */
    public function dispatch()
    {
        // Schedule the cron healthcheck.
        $this->scheduleEvent();

        // Perform remote post.
        return parent::dispatch();
    }

    /**
     * @param mixed $payload
     * @return $this|Queue|Job
     *
     * @author rumur
     */
    public function push($payload)
    {
        array_push($this->payload, $payload);

        return $this;
    }

    /**
     * Update queue
     *
     * @param string $store_key Key.
     * @param mixed  $payload Payload.
     *
     * @return $this
     */
    public function update($store_key, $payload)
    {
        if (! empty($payload)) {
            \update_site_option($store_key, $payload);
        }

        return $this;
    }

    /**
     * Save queue
     *
     * @return $this
     */
    public function save()
    {
        $store_key = $this->getStoreKey();

        if (! empty($this->payload)) {
            \update_site_option($store_key, $this->payload);
        }

        return $this;
    }

    /**
     * Delete queue
     *
     * @param string $store_key Key.
     *
     * @return $this
     */
    public function delete($store_key)
    {
        \delete_site_option($store_key);

        return $this;
    }

    /**
     * Gets the stored key.
     *
     * @return string
     *
     * @author rumur
     */
    public function getStoreKey()
    {
        if (is_null($this->store_key)) {
            $this->store_key = $this->genKey();
        }

        return $this->store_key;
    }

    /**
     * Generate Store key
     *
     * Generates a unique key based on microtime. Queue items are
     * given a unique key so that they can be merged upon save.
     *
     * @param int $length Length.
     *
     * @return string
     */
    protected function genKey($length = 64)
    {
        $unique  = md5( microtime() . rand() );
        $prepend = $this->pointer . '_batch_';

        return substr( $prepend . $unique, 0, $length );
    }

    /**
     * Maybe process queue
     *
     * Checks whether data exists within the queue and that
     * the process is not already running.
     */
    public function maybeHandle()
    {
        // Don't lock up other requests while processing
        session_write_close();

        if (! \wp_check_password($this->pointer, $_REQUEST['token'])) {
            \wp_die('-1');
        }

        if ($this->isProcessRunning()) {
            \wp_die();
        }

        if ($this->isQueueEmpty()) {
            // No data to process.
            \wp_die();
        }

        $this->handle();

        \wp_die();
    }

    /**
     * Is process running
     *
     * Check whether the current process is already running
     * in a background process.
     */
    protected function isProcessRunning() {
        return \get_site_transient($this->pointer . '_process_lock')
            ? true
            : false;
    }

    /**
     * Is queue empty
     *
     * @return bool
     */
    protected function isQueueEmpty() {
        global $wpdb;

        $table = $wpdb->options;
        $column = 'option_name';

        if (\is_multisite()) {
            $table = $wpdb->sitemeta;
            $column = 'meta_key';
        }

        $key = $wpdb->esc_like( $this->pointer . '_batch_' ) . '%';

        $count = $wpdb->get_var($wpdb->prepare("
			SELECT COUNT(*)
			FROM {$table}
			WHERE {$column} LIKE %s
		", $key));

        return ( $count > 0 ) ? false : true;
    }

    /**
     * Handle
     *
     * Pass each queue item to the task handler, while remaining
     * within server memory and time limit constraints.
     */
    protected function handle()
    {
        $this->lockProcess();

        do {
            $batch = $this->getBatch();

            foreach ( $batch->data as $key => $value ) {

                $task = $this->task( $value );

                if ( static::TASK_DONE !== $task ) {
                    $batch->data[ $key ] = $task;
                } else {
                    unset( $batch->data[ $key ] );
                }

                if ( $this->timeExceeded() || $this->memoryExceeded() ) {
                    // Batch limits reached.
                    break;
                }
            }

            // Update or delete current batch.
            if (! empty($batch->data)) {
                $this->update($batch->key, $batch->data);
            } else {
                $this->delete($batch->key);
            }

        } while (! $this->timeExceeded() && ! $this->memoryExceeded() && ! $this->isQueueEmpty());

        $this->unlockprocess();

        // Start next batch or complete process.
        if (! $this->isQueueEmpty()) {
            $this->dispatch();
        } else {
            $this->complete();
        }

        \wp_die();
    }

    /**
     * Lock process
     *
     * Lock the process so that multiple instances can't run simultaneously.
     * Override if applicable, but the duration should be greater than that
     * defined in the time_exceeded() method.
     *
     * @return $this
     */
    protected function lockProcess()
    {
        $this->start_time = time(); // Set start time of current process.

        $lock_duration = \apply_filters($this->pointer . '_queue_lock_time', $this->queue_lock_time);

        \set_site_transient($this->pointer . '_process_lock', microtime(), $lock_duration);

        return $this;
    }

    /**
     * Unlock process
     *
     * Unlock the process so that other instances can spawn.
     *
     * @return $this
     */
    protected function unlockProcess() {
        \delete_site_transient( $this->pointer . '_process_lock' );

        return $this;
    }

    /**
     * Get batch
     *
     * @return stdClass Return the first batch from the queue
     */
    protected function getBatch()
    {
        global $wpdb;

        $table = $wpdb->options;
        $column = 'option_name';
        $key_column = 'option_id';
        $value_column = 'option_value';

        if ( \is_multisite() ) {
            $table = $wpdb->sitemeta;
            $column = 'meta_key';
            $key_column = 'meta_id';
            $value_column = 'meta_value';
        }

        $key = $wpdb->esc_like($this->pointer . '_batch_') . '%';

        $query = $wpdb->get_row($wpdb->prepare("
			SELECT *
			FROM {$table}
			WHERE {$column} LIKE %s
			ORDER BY {$key_column} ASC
			LIMIT 1
		", $key));

        $batch = new \stdClass();

        $batch->key = $query->$column;
        $batch->data = \maybe_unserialize($query->$value_column);

        return $batch;
    }

    /**
     * Memory exceeded
     *
     * Ensures the batch process never exceeds 90%
     * of the maximum WordPress memory.
     *
     * @return bool
     */
    protected function memoryExceeded()
    {
        $memory_limit = $this->getMemoryLimit() * 0.9; // 90% of max memory
        $current_memory = memory_get_usage( true );

        $return = false;

        if ($current_memory >= $memory_limit) {
            $return = true;
        }

        return \apply_filters($this->pointer . '_memory_exceeded', $return);
    }

    /**
     * Get memory limit
     *
     * @return int
     */
    protected function getMemoryLimit()
    {
        if (function_exists('ini_get')) {
            $memory_limit = ini_get('memory_limit');
        } else {
            // Sensible default.
            $memory_limit = '128M';
        }

        if (! $memory_limit || -1 === intval($memory_limit)) {
            // Unlimited, set to 32GB.
            $memory_limit = '32000M';
        }

        return intval($memory_limit) * 1024 * 1024;
    }

    /**
     * Time exceeded.
     *
     * Ensures the batch never exceeds a sensible time limit.
     * A timeout limit of 30s is common on shared hosting.
     *
     * @return bool
     */
    protected function timeExceeded()
    {
        $finish = $this->start_time + \apply_filters($this->pointer . '_default_time_limit', 20); // 20 seconds

        $return = false;

        if (time() >= $finish) {
            $return = true;
        }

        return \apply_filters($this->pointer . '_time_exceeded', $return);
    }

    /**
     * Complete.
     *
     * Override if applicable, but ensure that the below actions are performed
     */
    protected function complete()
    {
        // Unschedule the cron healthcheck.
        $this->clearScheduledEvent();
    }

    /**
     * Schedule cron healthcheck
     *
     * @access public
     * @param mixed $schedules Schedules.
     * @return mixed
     */
    public function scheduleCronHealthCheck($schedules)
    {
        $interval = \apply_filters($this->pointer . '_cron_interval', $this->cron_healthcheck_interval);

        // Adds every 5 minutes (by default) to the existing schedules.
        $schedules[$this->cron_interval_identifier] = [
            'interval' => MINUTE_IN_SECONDS * $interval,
            'display' => sprintf(__('Every %d Minutes'), $interval),
        ];

        return $schedules;
    }

    /**
     * Handle cron healthcheck
     *
     * Restart the background process if not already running
     * and data exists in the queue.
     */
    public function handleCronHealthCheck()
    {
        if ($this->isProcessRunning()) {
            // Background process already running.
            exit;
        }

        if ($this->isQueueEmpty()) {
            // No data to process.
            $this->clearScheduledEvent();
            exit;
        }

        $this->handle();

        exit;
    }

    /**
     * Schedule event
     *
     * @return $this
     */
    protected function scheduleEvent()
    {
        if (! \wp_next_scheduled($this->cron_hook_identifier)) {
            \wp_schedule_event( time(), $this->cron_interval_identifier, $this->cron_hook_identifier );
        }

        return $this;
    }

    /**
     * Clear scheduled event
     *
     * @return $this
     */
    protected function clearScheduledEvent()
    {
        $timestamp = \wp_next_scheduled($this->cron_hook_identifier);

        if ($timestamp) {
            \wp_unschedule_event($timestamp, $this->cron_hook_identifier);
        }

        return $this;
    }

    /**
     * Cancel Process
     *
     * Stop processing queue items, clear cronjob and delete batch.
     *
     * @return $this
     */
    public function cancelProcess()
    {
        if (! $this->isQueueEmpty()) {

            $batch = $this->getBatch();

            $this->delete($batch->key);

            \wp_clear_scheduled_hook($this->cron_hook_identifier);
        }

        return $this;
    }

}
