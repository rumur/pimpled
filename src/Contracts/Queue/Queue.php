<?php

namespace Pmld\Contracts\Queue;

interface Queue
{
    /**
     * Push to queue
     *
     * @param mixed $payload Payload.
     *
     * @return $this
     */
    public function push($payload);

    /**
     * Update queue
     *
     * @param string $key Key.
     * @param mixed  $payload Payload.
     *
     * @return $this
     */
    public function update($key, $payload);

    /**
     * Delete queue
     *
     * @param string $key Key.
     *
     * @return $this
     */
    public function delete($key);

    /**
     * Save queue
     *
     * @return $this
     */
    public function save();

    /**
     * Dispatch
     *
     * @access public
     * @return void
     */
    public function dispatch();

    /**
     * Maybe process queue
     *
     * Checks whether Payload exists within the queue and the process is not in progress.
     */
    public function maybeHandle();
}
