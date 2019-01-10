
window._ = require('lodash');

/**
 * We'll load jQuery
 * This code may be modified to fit the specific needs of your application.
 */

try {
  window.$ = window.jQuery = require('jquery');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our WP back-end. This library automatically handles sending the
 * WP_NONCE token as a header based on the value of the "WP_NONCE" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
  window.axios.defaults.headers.common['X-WP-Nonce'] = token.content;
} else {
  console.error('CSRF token not found: https://codex.wordpress.org/WordPress_Nonces');
  console.error('CSRF token not found: https://en.wikipedia.org/wiki/Cross-site_request_forgery');
}
