# Scheduling (WP Cron)

- [How to use it?](#how-to-use-it)
- [Available options](#available-options)
- [Hooks](#hooks)

<a name="how-to-use-it"></a>
### How to use it?

1) Enable support of such functionality within the `config/app.php` file like so

    ```php
    // config/app.php
     
    // ...
    
    'providers' => array_merge([
         // ...
         Rumur\Pimpled\Scheduling\SchedulingServiceProvider::class,
        
         // ...
    ]),
    
    // ...
    ```

3) Create a Task like is following

    ```php
   <?php
   
   namespace Pmld\App\Scheduled;
   
   use Exception;
   use Rumur\Pimpled\Scheduling\Job;
   
   class DummyJob extends Job
   {
       /**
       * @param null $email
       * @thtows Exception
       * @return mixed|void
       */
       public function handle($email = null)
       {
           // Do you task here
       }
   }
   ```

3) Make your tasks be available for the app by registering tasks within the `config/scheduling.php` file like so

    ```php
    <?php
    // config/scheduling.php
    
   return [ 
       // ...
       'jobs' => [
           Pmld\App\Scheduled\DummyJob::class,
           // ...
       ],
   ];
   ```

4) Now you can use this task wherever you want like the following

    ```php
   <?php
   use Pmld\App\Scheduled\DummyJob;
   use Rumur\Pimpled\Support\Facades\Schedule;
   use function Rumur\Pimpled\Support\schedule;
    
   // Make it via Facade
   Schedule::job(new DummyJob(['email' => 'dev@gmail.com']))->onceInWeek();
    
   // Resign specific job via Facade
   Schedule::resignJob(new DummyJob(['email' => 'dev@gmail.com'])); 
       
   // Make it via helper function
   schedule(new DummyJob(['email' => 'author@gmail.com']))->dailyAt('13:00');
    
   // Resign specific job via helper function
   schedule()->resignJob(new DummyJob(['email' => 'author@gmail.com']));
    
   ```

<a name="available-options"></a>
### Available options

| Method                        | Description                                                  |
|----------------------------   |------------------------------------------------------------  |
| `->everyMinute();`            | Run the task every minute                                    |
| `->everyFiveMinutes();`       | Run the task every five minutes                              |
| `->everyTenMinutes();`        | Run the task every ten minutes                               |
| `->everyFifteenMinutes();`    | Run the task every fifteen minutes                           |
| `->everyThirtyMinutes();`     | Run the task every thirty minutes                            |
| `->hourly();`                 | Run the task every hour                                      |
| `->hourlyAt(17);`             | Run the task every hour at 17 mins past the hour             |
| `->daily();`                  | Run the task every day                                       |
| `->dailyAt('13:00');`         | Run the task every day at 13:00                              |
| `->weekly();`                 | Run the task every week                                      |
| `->weeklyOn(1, '8:00');`      | Run the task every week on Monday at 8:00                    |
| `->monthly();`                | Run the task every month                                     |
| `->monthlyOn(4, '15:00');`    | Run the task every month on the 4th at 15:00                 |
| `->quarterly();`              | Run the task every quarter                                   |
| `->yearly();`                 | Run the task every year                                      |
| `->onceInMinute();`           | Run the task only once in minute                             |
| `->onceInMinutes(45);`        | Run the task only once in 45 minutes                         |
| `->onceInFiveMinutes();`      | Run the task only once in 5 minutes                          |
| `->onceInTenMinutes();`       | Run the task only once in 10 minutes                         |
| `->onceInFifteenMinutes();`   | Run the task only once in 15 minutes                         |
| `->onceInThirtyMinutes();`    | Run the task only once in 30 minutes                         |
| `->onceInHour();`             | Run the task only once in one hour                           |
| `->onceInDay();`              | Run the task only once in one day                            |
| `->onceInWeek();`             | Run the task only once in one week                           |
| `->onceInMonth();`            | Run the task only once in one month                          |
| `->onceInQuarter();`          | Run the task only once in a quarter                          |
| `->onceInYear();`             | Run the task only once in a year                             |


### How to unschedule your tasks

| Method                                                                   | Description                                                                           |
|------------------------------------------------------------------------  |-------------------------------------------------------------------------------------  |
| `->resignJob(new DummyJob(['email' => 'dev@gmail.com']));`               | Resign one specific job, Pass a specific task with params you're registered           |
| `->resignAllJobs(new DummyJob);`                                         | Resign all jobs for this task, Pass a specific task only                              |
| `Schedule::resignJob(new DummyJob(['email' => 'dev@gmail.com']));`       | Resign one specific job using Facade                                                  |

<a name="hooks"></a>
### Hooks

There are several actions and filters that might be useful 

#### Filters

| Hook                                                                     | Description                                                                           |
|------------------------------------------------------------------------  |-------------------------------------------------------------------------------------  |
| `apply_filters('pmld.scheduling.resolving_task', $task);`                | Fires when the task is about to use, it's a good place to make some changes           |
| `apply_filters('pmld.scheduling.hourly_at', int $timestamp, int $min, int $orig_min);` | Fires for a `hourlyAt` recurrence                            |
| `apply_filters('pmld.scheduling.daily_at', int $timestamp, string $time);` | Fires for a `dailyAt` recurrence                                         |
| `apply_filters('pmld.scheduling.weekly_on', int $timestamp, int $day_num, string $time);` | Fires for a `weeklyOn` recurrence                         |
| `apply_filters('pmld.scheduling.monthly_on', int $timestamp, int $month_day, int $time);` | Fires for a `monthlyOn` recurrence                        |
| `apply_filters('pmld.scheduling.recurrence_multiple', [...]);` | Fires when recurrence is retrieved                                                   |
| `apply_filters('pmld.scheduling.recurrence_single', [...]);` | Fires when recurrence is retrieved                                                     |
| `apply_filters('pmld.scheduling.recurrence_calculated', [...]);` | Fires when recurrence is retrieved                                                 |
| `apply_filters('pmld.scheduling.now', time());` | Fires when current time is retrieved                                                                |

#### Actions

| Hook                                                                     | Description                                                                           |
|------------------------------------------------------------------------  |-------------------------------------------------------------------------------------  |
|`do_action('pmld.scheduling.before_dispatch', $job, $args);`              | Fires right before the job is dispatched                                              |
|`do_action('pmld.scheduling.after_dispatch', $job, $args);`               | Fires right after the job has been dispatched                                         |
|`do_action('pmld.scheduling.dispatched_failed', $job, $args, Exception $e);` | Fires when a job has been failed                                                   |
|`do_action("pmld.scheduling.dispatched_failed_{$specific_job_action}", $job, $args, Exception $e)` | Fires when a job has been failed                             |

