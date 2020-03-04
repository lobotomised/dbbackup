<?php

namespace Lobotomised\Dbbackup\Test;

use Illuminate\Support\Facades\Storage;
use Lobotomised\Dbbackup\Backup;

class BackupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('dbbackup');
    }

    /** @test **/
    public function it_delete_old_files(): void
    {
        Storage::disk('dbbackup')->put('19700102-000000.sql','');
        Storage::disk('dbbackup')->put('19700101-000000.sql','');

        (new Backup)->removeOld(1);

        Storage::disk('dbbackup')->assertMissing('19700101-000000.sql');
        Storage::disk('dbbackup')->assertExists('19700102-000000.sql');
    }

}
