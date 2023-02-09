<?php

declare(strict_types=1);

namespace Lobotomised\Dbbackup;

use Lobotomised\Dbbackup\Commands\RunBackup;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        app()->config['filesystems.disks.dbbackup'] = [
            'driver' => 'local',
            'root' => storage_path('backups/'),
        ];

        if ($this->app->runningInConsole()) {
            $this->commands([
                RunBackup::class,
            ]);
        }
    }
}
