<?php

namespace haxibiao\task;

use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            Console\TaskInstall::class,
            Console\TaskPublish::class,
        ]);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Console/stubs/TaskServiceProvider.stub' => app_path('Providers/TaskServiceProvider.php'),
        ], 'task-provider');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/task.php' => config_path('task.php'),
            ], 'task-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'task-migrations');

            $this->publishes([
                __DIR__ . '/../graphql/task' => base_path('graphql/task'),
            ], 'task-graphql');

            $this->publishes([
                __DIR__ . '/../tests/Feature/GraphQL' => base_path('tests/Feature/GraphQL'),
            ], 'task-tests');

        }
    }
}
