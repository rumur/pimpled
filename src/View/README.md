# Views

- [Creating Views](#creating-views)
- [Passing Data To Views](#passing-data-to-views)
    - [Sharing Data With All Views](#sharing-data-with-all-views)
- [Hooks](#hooks)

<a name="creating-views"></a>
## Creating Views

Views contain the HTML served by your application and separate your controller / application logic from your presentation logic. Views are stored in the `resources/views` directory. A simple view might look something like this:

    <!-- View stored in resources/views/greeting.php -->

    <html>
        <?php wp_head(); ?>
        <body>
            <h1>Hello, <?= $name ?></h1>
            <?php wp_footer(); ?>
        </body>
    </html>

Since this view is stored at `resources/views/greeting.php`, we may return it using the `view` helper like so:

```php
<?php
// routes/web.php

use Rumur\Pimpled\Support\Facades\Route;
use function Rumur\Pimpled\Support\view;

Route::get('greeting', [
    'callback' => function () {
        return view('greeting', ['name' => 'John']);
    }
]);

```
    
As you can see, the first argument passed to the `view` helper corresponds to the name of the view file in the `resources/views` directory. The second argument is an array of data that should be made available to the view. In this case, we are passing the `name` variable.

Views may also be nested within sub-directories of the `resources/views` directory. "Dot" notation may be used to reference nested views. For example, if your view is stored at `resources/views/admin/profile.php`, you may reference it like so:

    return view('admin.profile', $data);

#### Determining If A View Exists

If you need to determine if a view exists, you may use the `View` facade. The `exists` method will return `true` if the view exists:

```php
<?php
use Rumur\Pimpled\Support\Facades\View;

if (View::exists('emails.customer')) {
    //
}
```

#### Creating The First Available View

Using the `first` method, you may create the first view that exists in a given array of views. This is useful if your application or package allows views to be customized or overwritten:

```php
<?php
use function Rumur\Pimpled\Support\view;

return view()->first(['custom.admin', 'admin'], $data);
```

You may also call this method via the `View`:

```php

<?php
use Rumur\Pimpled\Support\Facades\View;

return View::first(['custom.admin', 'admin'], $data);

```

> In order to overwrite the view for an app, it's possible by creating the same folder as a `Plugin Name` or `Theme Name` within the theme e.g. `acme` 
> and then you need recreate the same structure to this file e.g. if you have `resources/views/admin/dashboard.php` than you need `themes/yourtheme/acme/admin/dashboard.php`, Note that it also works for a child themes as well.

<a name="passing-data-to-views"></a>
## Passing Data To Views

As you saw in the previous examples, you may pass an array of data to views:

    return view('greetings', ['name' => 'Victoria']);

When passing information in this manner, the data should be an array with key / value pairs. Inside your view, you can then access each value using its corresponding key, such as `<?php echo $key; ?>`. As an alternative to passing a complete array of data to the `view` helper function, you may use the `with` method to add individual pieces of data to the view:

    return view('greeting')->with('name', 'Victoria');

<a name="sharing-data-with-all-views"></a>
#### Sharing Data With All Views

Occasionally, you may need to share a piece of data with all views that are rendered by your application. You may do so using the view facade's `share` method. Typically, you should place calls to `share` within a service provider's `boot` method. You are free to add them to the `AppServiceProvider` or generate a separate service provider to house them:

    <?php

    namespace Pmld\App\Providers;

    use Rumur\Pimpled\Support\Facades\View;
    use Rumur\Pimpled\Support\ServiceProvider;

    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
            //
        }

        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            View::share('key', 'value');
        }
    }

<a name="hooks"></a>
### Hooks

There are several filters that might be useful 

#### Filters

| Hook                                                                     | Description                                                                           |
|------------------------------------------------------------------------  |-------------------------------------------------------------------------------------  |
| `apply_filters("pmld.view.{view_name}_params", $data, $view);`         | Fires when the retrieving params that were passed to that view                        |
| `apply_filters("pmld.view.{view_name}_compiled_params", $data, $view);` | Fires when the all shared all globally and locally data is about to inject to it's view |
| `apply_filters("pmld.view.{view_name}_with_errors", $errors, $view);` | Fires when errors is about to added to a view params, Note it fires only for a `withErrors` method |
