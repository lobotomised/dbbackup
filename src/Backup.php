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

        $client = $this->createMysqlConfFile(config('database.connections.mysql.username'), config('database.connections.mysql.password'));

        $command = sprintf(
            'mysqldump --defaults-file=%s --default-character-set=utf8mb4 --host=%s --port=%s %s > %s',
            $client,
            config('database.connections.mysql.host'),
            config('database.connections.mysql.port'),
            config('database.connections.mysql.database'),
            Storage::disk('dbbackup')->path(date('Ymd-His').'.sql')
        );

        $process = Process::fromShellCommandline($command);

        $process->mustRun();

        $this->removeMysqlConfFile();

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

    private function gitignore(): void
    {
        if(!Storage::disk('dbbackup')->exists('.gitignore')) {
            Storage::disk('dbbackup')->put('.gitignore', "*\n!.gitignore");
        }
    }

    public function createMysqlConfFile(string $user, string $password): string
    {
        if(!Storage::disk('dbbackup')->exists('mysqldump.cnf')) {
            $content = <<< EOT
[client]
user="$user"
password="$password"

EOT;

            Storage::disk('dbbackup')->put('mysqldump.cnf', $content);
        }

        return Storage::disk('dbbackup')->path('mysqldump.cnf');
    }

    private function removeMysqlConfFile(): void
    {
        if(Storage::disk('dbbackup')->exists('mysqldump.cnf')) {
            Storage::disk('dbbackup')->delete('mysqldump.cnf');
        }
    }

}
