<?php

namespace haxibiao\task\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TaskInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '任务模块的安装脚本 db,views ...';

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

        $this->comment('发布 Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'task-provider']);

        $this->comment('发布 资源文件 ...');
        $this->callSilent('task:publish', ['--force' => true]);

        $this->comment("注册 TaskServiceProvider ...");
        $this->registerServiceProvider();
    }

    protected function registerServiceProvider()
    {
        //避免重复添加
        $appConfigPHPStr = file_get_contents(config_path('app.php'));
        if (Str::contains($appConfigPHPStr, 'haxibiao\task\TaskServiceProvider::class')) {
            return;
        }

        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL . "        haxibiao\task\TaskServiceProvider::class," . PHP_EOL,
            file_get_contents(config_path('app.php'))
        ));
    }
}
