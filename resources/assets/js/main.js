// import external dependencies
import 'jquery';

// Import everything from autoload
// import "./autoload/**/*"

// import local dependencies
import Router from './main/util/Router';
import common from './main/routes/common';
import home from './main/routes/home';

/** Populate Router instance with DOM routes */
const routes = new Router({
  // All pages
  common,
  // Home page
  home,
});

// Load Events
jQuery(document).ready(() => routes.loadEvents());
