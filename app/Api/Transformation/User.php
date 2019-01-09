<?php

namespace Pmld\App\Api\Transformation;

use Pmld\Support\Transformer;

class User extends Transformer
{
    /** @var \WP_User */
    protected $wp_user;

    /**
     * The hidden user data fields
     * @var array
     */
    protected $hidden = [
        'user_pass',
        'user_status',
        'user_activation_key',
    ];

    /**
     * User constructor.
     * @param \WP_User $user
     */
    public function __construct(\WP_User $user)
    {
        $this->wp_user = $user;
    }

    /**
     * Factory
     *
     * @param \WP_User $user
     *
     * @return User
     */
    public static function make(\WP_User $user)
    {
        return new static($user);
    }

    /**
     * Gets the raw data.
     * Represents as \WP_User Object
     *
     * @return \WP_User
     */
    public function raw()
    {
        return $this->wp_user;
    }

    /**
     * Get the instance as an array.
     *
     * @uses \get_avatar_url()
     * @uses \wp_create_nonce()
     *
     * @return array
     */
    public function toArray()
    {
        $user_data = array_merge(
            $this->raw()->to_array(),
            [
                'roles' => $this->raw()->roles,
                'avatar' => \get_avatar_url($this->raw()->ID),
                'token' => \wp_create_nonce('wp_rest'),
            ]
        );

        return $this->process($user_data);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
