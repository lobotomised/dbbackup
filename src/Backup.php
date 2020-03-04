<?php

declare(strict_types=1);

namespace Lobotomised\Dbbackup;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class Backup
{

    public function run(): bool
    {
        $this->gitignore();

        $command = sprintf(
            'mysqldump --default-character-set=utf8mb4 -u%s -p%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            Storage::disk('dbbackup')->getDriver()->getAdapter()->getPathPrefix() . date('Ymd-His').'.sql'
        );

        $process = Process::fromShellCommandline($command);

        $process->mustRun();

        return $process->isSuccessful();
    }

    public function removeOld(int $keep): void
    {
        $files = Storage::disk('dbbackup')->files();

        $sql_files = [];
        foreach ($files as $file) {
            if (preg_match('/([\d]{4})([\d]{2})([\d]{2})-([\d]{2})([\d]{2})([\d]{2}).sql/D', $file, $matches)) {
                $sql_files[ $matches[1].$matches[2].$matches[3].$matches[4].$matches[5].$matches[6]] = $file;
            }
        }

        krsort($sql_files);

        $to_delete = array_slice($sql_files, $keep);

        foreach ($to_delete as $file) {
            Storage::disk('dbbackup')->delete($file);
        }
    }

    private function gitignore()
    {
        if(Storage::disk('dbbackup')->missing('.gitignore')) {
            Storage::disk('dbbackup')->put('.gitignore', "*\n!.gitignore");
        }
    }
}
