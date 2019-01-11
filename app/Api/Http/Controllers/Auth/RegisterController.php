<?php

namespace Pmld\App\Api\Http\Controllers\Auth;

use Pmld\App\Api\Transform\User;
use Pmld\Foundation\Http\Controller\BaseController;
use Pmld\Foundation\Http\Exceptions\UnauthorizedException;

class RegisterController extends BaseController
{
    /**
     * Registered user into the system.
     *
     * @return mixed|\WP_REST_Response
     *
     * @throws UnauthorizedException
     */
    public function register()
    {
        $input = (object) array_intersect_key(
            array_fill_keys(['username', 'password', 'email'], false),
            $this->request->get_body_params()
        );

        $update = [
            'first_name',
            'last_name',
            'name',
        ];

        /** @var \WP_Error|integer $user */
        $user_id = \wp_create_user($input->username, $input->password, $input->email);

        if (\is_wp_error($user_id)) {
            throw new UnauthorizedException($user_id->get_error_message());
        }

        /** @var \WP_User $user */
        $user = \get_userdata($user_id);

        foreach ($update as $att) {
            $user->$att = $input->$att;
        }

        \wp_update_user($user);

        return $this->response->ok([
            'message' => sprintf(__('Hello %s', PMLD_TD), $user->nickname),
            'user' => User::make($user),
        ]);

    }
}
