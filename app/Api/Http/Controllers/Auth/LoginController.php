<?php

namespace Pmld\App\Api\Http\Controllers\Auth;

use Pmld\App\Api\Transform\User;
use Pmld\Foundation\Http\Controller\BaseController;
use Pmld\Foundation\Http\Exceptions\UnauthorizedException;

class LoginController extends BaseController
{
    /**
     * @param string $username
     * @param string $password
     *
     * @uses \__()
     * @uses \is_wp_error()
     * @uses \wp_authenticate()
     *
     * @return mixed|\WP_REST_Response
     * @throws UnauthorizedException
     */
    public function login($username, $password)
    {
        /** @var \WP_User|\WP_Error $user */
        $user = \wp_authenticate($username, $password);

        if (\is_wp_error($user)) {
            throw new UnauthorizedException($user->get_error_message(), [
                'params' => compact('username', 'password')
            ]);
        }

        return $this->response->ok([
            'message' => sprintf(__('Welcome back %s', PMLD_TD), $username),
            'data' => [
                'user' => User::make($user),
            ]
        ]);
    }
}
