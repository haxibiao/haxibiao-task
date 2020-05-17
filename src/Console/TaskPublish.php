<?php
namespace haxibiao\task\Console;

use Illuminate\Console\Command;

class TaskPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:publish {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发布 resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // 就配置文件自定义后，不方便覆盖，更新需要单独
        // vendor:publish --tag=task-config --force=true
        $this->call('vendor:publish', [
            '--tag'   => 'task-config',
            '--force' => false,
        ]);

        $this->call('vendor:publish', [
            '--tag'   => 'task-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag'   => 'task-graphql',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag'   => 'task-tests',
            '--force' => $this->option('force'),
        ]);

    }
}
