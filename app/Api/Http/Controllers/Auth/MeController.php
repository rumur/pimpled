<?php

namespace Pmld\App\Api\Http\Controllers\Auth;

use Pmld\App\Api\Transform\User;
use Pmld\Http\Controller\BaseController;
use Pmld\Http\Exceptions\UnauthorizedException;

class MeController extends BaseController
{
    /**
     * Gets the current user information.
     *
     * @uses \__()
     * @uses \wp_get_current_user()
     *
     * @return mixed|\WP_REST_Response
     *
     * @throws UnauthorizedException
     */
    public function me()
    {
        $user = \wp_get_current_user();

        if (! $user->ID) {
            throw new UnauthorizedException(__('Your are not logged in!', PMLD_TD));
        }

        return $this->response->ok([
           'data' => User::make($user),
        ]);
    }
}
