<?php

namespace Pmld\Foundation\Http;

use \WP_REST_Request;
use Pmld\Contracts\Http\Request as RequestContracts;

class Request extends WP_REST_Request implements RequestContracts
{
    /**
     * Factory.
     *
     * @return Request
     */
    public static function make()
    {
        if (isset($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } else {
            $path = '/';
        }

        $request = new static($_SERVER['REQUEST_METHOD'], $path);

        $request->set_query_params(\wp_unslash($_GET));
        $request->set_body_params(\wp_unslash($_POST));
        $request->set_file_params($_FILES);
        $request->set_headers(rest_get_server()->get_headers(\wp_unslash($_SERVER)));
        $request->set_body(rest_get_server()->get_raw_data());

        return $request;
    }

    /**
     * Gather All request's params.
     *
     * @return array
     */
    public function all()
    {
        try {
            return array_merge(
                $this->get_default_params(),
                $this->get_url_params(),
                $this->get_body_params(),
                $this->getFileParams()
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Collapsed $_FILE into array.
     *
     * @return array
     */
    public function getFileParams()
    {
        $raw = $this->get_file_params();

        if (! empty($raw)) {

            $collapsed = [];

            foreach ($raw as $name => $file) {

                $is_nested = isset($file['error']) && is_array($file['error']);

                if ($is_nested) {
                    foreach ($file as $file_key_name => $file_value) {
                        foreach ($file_value as $index => $value) {
                            $collapsed[$name][$index][$file_key_name] = $value;
                        }
                    }
                } else {
                    $collapsed[$name] = $file;
                }
            }

            return $collapsed;
        }

        return $raw;
    }
}
