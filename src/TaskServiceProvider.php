<?php

namespace Haxibiao\Task;

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
            Console\InstallCommand::class,
            Console\PublishCommand::class,
        ]);
        $this->bindPathsInContainer();
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
                __DIR__ . '/../database/factories' => database_path('./factories'),
            ], 'task-db');

            $this->publishes([
                __DIR__ . '/../graphql' => base_path('graphql'),
            ], 'task-graphql');

            $this->publishes([
                __DIR__ . '/../tests/Feature/GraphQL' => base_path('tests/Feature/GraphQL'),
            ], 'task-tests');

            //注册 migrations paths
            $this->loadMigrationsFrom($this->app->make('path.haxibiao-task.migrations'));
        }
    }

    /**
     * Bind paths in container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        foreach ([
            'path.haxibiao-task'            => $root = dirname(__DIR__),
            'path.haxibiao-task.config'     => $root . '/config',
            'path.haxibiao-task.graphql'    => $root . '/graphql',
            'path.haxibiao-task.database'   => $database = $root . '/database',
            'path.haxibiao-task.migrations' => $database . '/migrations',
            'path.haxibiao-task.seeds'      => $database . '/seeds',
        ] as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [DZTasksSeeder::class];
    }
}
