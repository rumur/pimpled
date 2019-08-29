<?php

defined('ABSPATH') or die();

use Rumur\Pimpled\Support\Facades\Route;

// Multilingual support
/*Route::useWPML();
Route::usePolyLang();

Route::get('team', [
    'callback' => 'Pmld\App\Http\Controllers\TeamController@index',
]);

Route::post('team', [
    'callback' => 'Pmld\App\Http\Controllers\TeamController@update',
]);

Route::get('team/{member_id}', [
    'name' => 'test.route',
    'regexp' => [
        'test_name' => '[\\w+]{1,}'
    ],
    'callback' => function ($test_name) {
        return $test_name;
    },
    'permission_callback' => function () {
        return is_user_logged_in();
    }
]);*/
