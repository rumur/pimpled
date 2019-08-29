<?php

namespace Rumur\Pimpled\Http;

use WP_Post;
use WP_User;
use Redirector;
use Notifications;
use Requests_Exception_HTTP;
use Requests_Exception_HTTP_403;

/**
 * Class Controller
 *
 * @author rumur
 */
class Controller
{
    /**
     * @var WP_User|null
     */
    protected $user;

    /** @var Redirector */
    protected $redirector;

    /** @var Notifications */
    protected $notifications;

    /** @var string */
    protected $redirect_to = '/';

    /** @var string  */
    protected $nonce_name = '_wp_nonce';

    /** @var mixed */
    protected $nonce_action = __CLASS__;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->resolveNotifications();
        $this->boot();
    }

    /**
     * Boot Method
     */
    protected function boot()
    {
        // ...
    }

    /**
     * Use for wp_nonce_*.* function as an nonce.
     *
     * @return mixed
     */
    protected function nonceName()
    {
        return $this->nonce_name;
    }

    /**
     * Use for wp_nonce_*.* function as an action.
     *
     * @return mixed
     */
    protected function nonceAction()
    {
        return $this->nonce_action;
    }

    /**
     * Nonce hidden fields to secure the form
     *
     * @return string
     */
    protected function nonceField()
    {
        $html = wp_nonce_field($this->nonceAction(), $this->nonceName(), true, false);

        return $html . sprintf('<input type="hidden" name="redirect_to" value="%s" />', esc_attr($this->redirectTo()));
    }

    /**
     * @return array
     */
    public function notifications()
    {
        return $this->resolveNotifications()->all();
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->resolveNotifications()->onlyErrors();
    }

    /**
     * @return array
     */
    public function getWarnings()
    {
        return $this->resolveNotifications()->onlyWarnings();
    }

    /**
     * @return array
     */
    public function getUpdates()
    {
        return $this->resolveNotifications()->onlyUpdates();
    }

    /**
     * Return the redirect_to url.
     *
     * @return string
     */
    protected function redirectTo()
    {
        $redirect_to = wp_validate_redirect($_REQUEST['redirect_to'] ?? $this->redirect_to);

        $redirect_to = $redirect_to ?: '/';

        return strpos($redirect_to, '?') === false ? trailingslashit($redirect_to) : $redirect_to;
    }

    /**
     * Redirect process
     *
     * @param string|null $where
     * @param int $status
     * @return void
     */
    protected function redirect($where = null, $status = 302)
    {
        $this->resolveRedirector()->redirect($where ?: $this->redirectTo(), $status);
    }

    /**
     * Redirect user to logged in page if user is not logged in
     *
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    protected function redirectNotLoggedIn($where = null, $status = 302)
    {
        $this->resolveRedirector()->redirectNotLoggedIn($where ?: add_query_arg([
            'redirect_to' => rawurlencode($_SERVER['REQUEST_URI'])
        ], route('auth.login')), $status);

        return $this;
    }

    /**
     * Redirect user to logged in page if user is not logged in
     *
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    protected function redirectLoggedIn($where = null, $status = 302)
    {
        $this->resolveRedirector()
            ->redirectLoggedIn($where ?: route('profile'), $status);

        return $this;
    }

    /**
     * Redirect user if he has a not valid role
     *
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    protected function redirectNotAllowedRole($where = null, $status = 302)
    {
        if (empty(array_intersect($this->resolveUser()->roles, $this->allowed_roles))) {
            $this->redirect($where, $status);
        }

        return $this;
    }

    /**
     * Redirect user if can't create
     *
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    protected function redirectCantCreate($where = null, $status = 302)
    {
        if (false === $this->resolveUser()->has_cap('create_posts')) {
            $this->redirect($where, $status);
        }

        return $this;
    }

    /**
     * Redirect user if he can't update
     *
     * @param int $id
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    protected function redirectCantEdit($id, $where = null, $status = 302)
    {
        if (false === $this->resolveUser()->has_cap('edit_post', $id)) {
            $this->redirect($where, $status);
        }

        return $this;
    }

    /**
     * Redirect user if he is not an owner
     *
     * @param int $id
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    protected function redirectNotOwner($id, $where = null, $status = 302)
    {
        $post = get_post($id);

        if ($post instanceof WP_Post && $post->post_author != $this->resolveUser()->ID) {
            $this->redirect($where, $status);
        }

        return $this;
    }

    /**
     * @return \Notifications
     */
    protected function resolveNotifications()
    {
        if (!$this->notifications) {
            $this->notifications = new Notifications();
        }

        return $this->notifications;
    }

    /**
     * Get the Current User or set the new one
     *
     * @param WP_User|null $user
     *
     * @return WP_User
     */
    protected function resolveUser(WP_User $user = null)
    {
        if ($user instanceof WP_User) {
            $this->user = $user;
        }

        if (null === $this->user) {
            $this->user = wp_get_current_user();
        }

        return $this->user;
    }

    /**
     * Get the Redirector
     *
     * @return Redirector
     */
    protected function resolveRedirector(): Redirector
    {
        if (!$this->redirector) {
            $this->redirector = (new Redirector())
                ->setRedirectTo($this->redirect_to);
        }

        return $this->redirector;
    }

    /**
     * Verifies the POST actions.
     *
     * @param string $action
     * @param string $action_name
     *
     * @return $this
     *
     * @throws Requests_Exception_HTTP_403
     */
    protected function verifyAction($action = null, $action_name = 'action')
    {
        if ($action) {
            $is_valid = isset($_REQUEST[$action_name]) && wp_verify_nonce($_REQUEST[$action_name], $action);
        } else {
            $is_valid = (boolean)wp_verify_nonce($_REQUEST[$this->nonceName()], $this->nonceAction());
        }

        if (!$is_valid) {
            throw new Requests_Exception_HTTP_403();
        }

        return $this;
    }

    /**
     * @param string $signature
     * @param string $action_name
     * @return $this
     * @throws Requests_Exception_HTTP_403
     */
    protected function verifySignature($signature, $action_name = 'signed')
    {
        try {
            return $this->verifyAction($signature, $action_name);
        } catch (Requests_Exception_HTTP $e) {
            throw new Requests_Exception_HTTP_403(__('Invalid Action.', EP_TEXT_DOMAIN));
        }
    }
}
