<?php

namespace Rumur\Pimpled\Http;

/**
 * Class Redirector
 *
 * @author rumur
 */
class Redirector
{
    /** @var string */
    protected $redirect_to = '/';

    /**
     * Redirect
     *
     * @param string|null $where
     * @param int $status
     */
    public function redirect($where = null, $status = 302)
    {
        if ($where !== null) {

            $where = wp_validate_redirect($where);

            $where = false !== filter_var($where, FILTER_VALIDATE_URL) ? $where : network_site_url($where);
        }

        if (empty($where)) {
            $where = false !== filter_var($redirect_to = $this->redirectTo(), FILTER_VALIDATE_URL)
                ? $redirect_to
                : network_site_url($redirect_to);
        }

        wp_redirect($where, $status);
        exit;
    }

    /**
     * Return the redirect_to url.
     *
     * @return string
     */
    public function redirectTo()
    {
        $redirect_to = wp_validate_redirect($_REQUEST['redirect_to'] ?? $this->redirect_to);

        $redirect_to = $redirect_to ?: '/';

        return strpos($redirect_to, '?') === false ? trailingslashit($redirect_to) : $redirect_to;
    }

    /**
     * Redirect user to logged in page if he is not logged in yet
     *
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    public function redirectNotLoggedIn($where = null, $status = 302)
    {
        if (!is_user_logged_in()) {
            $this->redirect($where ?: add_query_arg([
                'redirect_to' => rawurlencode($_SERVER['REQUEST_URI'])
            ], wp_login_url(), $status));
        }

        return $this;
    }

    /**
     * Redirect user if he is not a super admin.
     *
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    public function redirectNotSuperAdmin($where = null, $status = 302)
    {
        if (!is_super_admin()) {
            $this->redirect($where, $status);
        }

        return $this;
    }

    /**
     * Redirect user if he is a super admin.
     *
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    public function redirectSuperAdmin($where = null, $status = 302)
    {
        if (is_super_admin()) {
            $this->redirect($where, $status);
        }

        return $this;
    }

    /**
     * Redirect user to profile page if he is logged in
     *
     * @param null $where
     * @param int $status
     *
     * @return $this
     */
    public function redirectLoggedIn($where = null, $status = 302)
    {
        if (is_user_logged_in()) {
            $this->redirect($where, $status);
        }

        return $this;
    }

    /**
     * @param $to
     * @return $this
     */
    public function setRedirectTo($to)
    {
        $this->redirect_to = $to;

        return $this;
    }
}
