<?php

declare(strict_types=1);

namespace Lobotomised\Dbbackup\Commands;

use Illuminate\Console\Command;
use Lobotomised\Dbbackup\Backup;

class RunBackup extends Command
{
    protected $signature = 'db:backup
                {--delete : Remove old backup}
                {--keep=5 : Number of backup to keep}';

    protected $description = 'Backup the database';

    /** @var \Lobotomised\Dbbackup\Backup */
    private $backup;

    public function __construct(Backup $backup)
    {
        parent::__construct();

        $this->backup = $backup;
    }

    public function handle()
    {
        $this->info('Backup database start');

        if ($this->backup->run()) {
            $this->info('Backup database done');
        } else {
            $this->error('Backup failed');
        }

        if ($this->option('delete')) {
            $this->backup->removeOld((int) $this->option('keep'));
        }
    }
}
