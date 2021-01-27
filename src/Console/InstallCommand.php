<?php

namespace Haxibiao\Task\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:install {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '安装 haxibiao-task';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $force = $this->option('force');

        $this->info('迁移数据库...');
        $this->call('migrate');

        $this->info('发布 资源文件 ...');
        $this->callSilent('task:publish', ['--force' => $force]);

        $this->info("复制 stubs ...");
        copyStubs(__DIR__, false);
    }

    protected function resolveStubPath($stub)
    {
        return __DIR__ . $stub;
    }

    protected function registerServiceProvider()
    {
        //避免重复添加
        $appConfigPHPStr = file_get_contents(config_path('app.php'));
        if (Str::contains($appConfigPHPStr, 'TaskServiceProvider::class')) {
            return;
        }

        $namespace = "App";

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL . "        Haxibiao\\task\\TaskServiceProvider::class," . PHP_EOL,
            file_get_contents(config_path('app.php'))
        ));
    }
}
