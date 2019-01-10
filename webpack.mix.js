const mix = require('laravel-mix');
const path = require('path');
const webpack = require('webpack');
const merge = require('webpack-merge');
const CleanWebpackPlugin = require('clean-webpack-plugin');

const isProd = mix.inProduction();
const isWatchMode = process.argv.includes('--watch');
const isHotMode = ['hot', 'hot:https'].some((cmd) => process.env.npm_lifecycle_event === cmd);

/*
 |--------------------------------------------------------------------------
 | Helper function
 |--------------------------------------------------------------------------
 */
const desire = (dependency, fallback) => {
  try {
    require.resolve(dependency);
  } catch (err) {
    return fallback;
  }
  return require(dependency); // eslint-disable-line import/no-dynamic-require
};

const resolve = (dir) => path.join(__dirname, '..', dir);

const userConfig = merge(
  desire(`${__dirname}/resources/assets/config`),
  desire(`${__dirname}/resources/assets/config-local`)
);

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your WP application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 |
 | Documentation you can @link https://laravel.com/docs/5.6/mix#installation
 */

mix
  .setResourceRoot(userConfig.rootPath)
  .setPublicPath(userConfig.publicPath)
  .webpackConfig({
    externals: {
      jquery: 'jQuery',
    },
    resolve: {
      alias: {
        '@': resolve(`${userConfig.assets}/js/vue`),
      }
    },
    devtool: ( ! isProd ? userConfig.sourceMap : undefined),
    plugins: [
      new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
      }),
      new CleanWebpackPlugin([
        `${userConfig.publicPath}/js`,
        `${userConfig.publicPath}/css`,
      ], {verbose: false})
    ],
    ...( isHotMode
      ? { devServer: { contentBase: path.resolve(__dirname, userConfig.publicPath) } }
      : {}
    )
  })
  .sass(`${userConfig.assets}/scss/app.scss`, 'dist/css/app.css')
  .sass(`${userConfig.assets}/scss/main.scss`, 'dist/css/main.css')
  .sass(`${userConfig.assets}/scss/admin.scss`, 'dist/css/admin.css')
  .options({processCssUrls: false, /* it keeps the relative urls */})
  .js(`${userConfig.assets}/js/main.js`, 'dist/js/main.js')
  .js(`${userConfig.assets}/js/app.js`, 'dist/js/app.js')
  .extract(userConfig.extract)
  .copyDirectory(`${userConfig.assets}/images`, `${userConfig.publicPath}/images`)
  .copyDirectory(`${userConfig.assets}/fonts`, `${userConfig.publicPath}/fonts`);

if (! userConfig.buildNotifications) {
  mix.disableNotifications();
}

switch(true) {

  case isProd:
    // Adds versions to the all assets and /img content as well.
    mix.version();
    mix.version([`${userConfig.publicPath}/images`]);
  break;

  case isWatchMode:
    // Runs the Watch mode.
    mix.browserSync({
      proxy: userConfig.devUrl,
      files: userConfig.watch,
    });
    mix.sourceMaps();
  break;

  default:
    // Default
  break;
}

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.preact(src, output); <-- Identical to mix.js(), but registers Preact compilation.
// mix.coffee(src, output); <-- Identical to mix.js(), but registers CoffeeScript compilation.
// mix.ts(src, output); <-- TypeScript support. Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.standaloneSass('src', output); <-- Faster, but isolated from Webpack.
// mix.fastSass('src', output); <-- Alias for mix.standaloneSass().
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.test');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.extend(name, handler) <-- Extend Mix's API with your own components.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   uglify: {}, // Uglify-specific options. https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });

