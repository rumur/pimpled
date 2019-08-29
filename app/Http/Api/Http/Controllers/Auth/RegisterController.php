<?php

namespace Pmld\App\Api\Http\Controllers\Auth;

use Pmld\App\Api\Transform\User;
use Pmld\Http\Controller\BaseController;
use Pmld\Http\Exceptions\UnauthorizedException;

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
        $create_from = $this->request->get_body_params();

        $input = (object) array_intersect_key( $create_from,
            array_fill_keys(['username', 'password', 'email'], false)
        );

        $update = [
            'first_name',
            'last_name',
        ];

        /** @var \WP_Error|integer $user */
        $user_id = \wp_create_user($input->username, $input->password, $input->email);

        if (\is_wp_error($user_id)) {
            throw new UnauthorizedException($user_id->get_error_message());
        }

        /** @var \WP_User $user */
        $user = \get_userdata($user_id);

        foreach ($update as $att) {
            $user->$att = $create_from[$att];
        }

        \wp_update_user($user);

        return $this->response->ok([
            'message' => sprintf(__('Hello %s', PMLD_TD), $user->nickname),
            'user' => User::make($user),
        ]);

    }
}
