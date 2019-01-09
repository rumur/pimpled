<?php

use Pmld\Foundation\Http\Route;

/**
 * Register Auth Group of routes.
 */
Route::group('pmld/v1/auth', $middleware = [], function($namespace) {

    // Cuts an access for logged in users.
    $guest_middleware = [
        Pmld\App\Api\Http\Middleware\LoggedOutMiddleware::class,
    ];

    // Cuts an access for not logged in users.
    $user_middleware = [
        Pmld\App\Api\Http\Middleware\LoggedInMiddleware::class,
    ];

    /**
     * Registers in a new user.
     *
     * @since v1.0.0
     */
    Route::post( $namespace, 'register', [
        'uses' => '\Pmld\App\Api\Http\Controllers\Auth\RegisterController@register',
        'middleware' => $guest_middleware,
        'args' => [
            'email' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The User\'s account email',
                'sanitize_callback' => 'sanitize_email',
                'validate_callback' => function($email) {
                    return \is_email($email) && ! \email_exists($email);
                },
            ],
            'first_name' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The User\'s first name',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'last_name' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The User\'s last name',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'username' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The User\'s account name',
                'sanitize_callback' => 'sanitize_user',
                'validate_callback' => function($username) {
                    return \username_exists($username)
                        ? new \WP_Error('invalid_username', "The username \"{$username}\" is already taken.")
                        : true;
                }
            ],
            'password' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The User\'s account password',
            ],
            'password_confirmation' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The User\'s account password confirmation',
                'validate_callback' => function($pass_conf, \WP_REST_Request $request) {
                    $passwords = array_map('trim', [$pass_conf, $request->get_param('password')]);

                    return count(array_unique($passwords)) !== 1
                        ? new \WP_Error('invalid_password_confirmation',"The passwords should be the same.")
                        : true;
                }
            ],
        ]
    ]);

    Route::post( $namespace, 'test', [
        'uses' => '\Pmld\App\Api\Http\Controllers\Auth\RegisterController@test',
        'middleware' => $guest_middleware,
        'args' => [
            'user' => [
                'type' => 'object',
                'required' => true,
            ],
        ]
    ]);

    /**
     * Logs in the current user.
     *
     * @since v1.0.0
     */
    Route::post( $namespace, 'login', [
        'uses' => '\Pmld\App\Api\Http\Controllers\Auth\LoginController@login',
        'middleware' => $guest_middleware,
        'args' => [
            'username' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The User\'s account name or email',
                'sanitize_callback' => 'sanitize_user',
                'validate_callback' => function($username) {
                    return \username_exists($username);
                }
            ],
            'password' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The User\'s account password',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function($password, \WP_REST_Request $request) {
                    $password = trim($password);
                    $username = \sanitize_user($request->get_param('username'));

                    $user = \wp_authenticate_username_password(null, $username, $password);

                    return \is_wp_error($user) ? $user : true;
                }
            ],
        ]
    ]);

    /**
     * Validates the current user.
     *
     * @since v1.0.0
     */
    Route::get( $namespace, 'me', [
        'uses' => '\Pmld\App\Api\Http\Controllers\Auth\MeController@me',
        'middleware' => $user_middleware,
    ]);
});
