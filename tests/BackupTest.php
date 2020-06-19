<?php

declare(strict_types=1);

namespace Lobotomised\Dbbackup\Test;

use Illuminate\Support\Facades\Storage;

class BackupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('dbbackup');
    }

    /** @test **/
    public function it_can_generate_backup(): void
    {
        $this->artisan('db:backup')
            ->assertExitCode(0);

        $files = Storage::disk('dbbackup')->files();

        $matches = preg_grep('/.sql$/i', $files);

        $this->assertCount(1, $matches);
    }

    /** @test **/
    public function it_create_gitignore_file(): void
    {
        $this->artisan('db:backup')
            ->assertExitCode(0);

        Storage::disk('dbbackup')->assertExists('.gitignore');
    }

    /** @test **/
    public function it_dont_delete_file(): void
    {
        Storage::disk('dbbackup')->put('19700101-000000.sql','');

        $this->artisan('db:backup');

        Storage::disk('dbbackup')->assertExists('19700101-000000.sql');
    }

    /** @test **/
    public function it_delete_old_file(): void
    {
        Storage::disk('dbbackup')->put('19700105-000000.sql','');
        Storage::disk('dbbackup')->put('19700104-000000.sql','');
        Storage::disk('dbbackup')->put('19700103-000000.sql','');
        Storage::disk('dbbackup')->put('19700102-000000.sql','');
        Storage::disk('dbbackup')->put('19700101-000000.sql','');

        $this->artisan('db:backup --delete');

        $files = Storage::disk('dbbackup')->files();

        $matches = preg_grep('/.sql$/i', $files);

        $this->assertCount(5, $matches);
    }

    /** @test **/
    public function it_can_delete_old_file_and_keep_some(): void
    {
        Storage::disk('dbbackup')->put('19700102-000000.sql','');
        Storage::disk('dbbackup')->put('19700101-000000.sql','');

        $this->artisan('db:backup --delete --keep=1');

        $files = Storage::disk('dbbackup')->files();

        $matches = preg_grep('/.sql$/i', $files);

        $this->assertCount(1, $matches);
    }

    /** @test **/
    public function it_generate_a_note_empty_file(): void
    {
        $this->artisan('db:backup');

        $value = Storage::disk('dbbackup')->get(
            Storage::disk('dbbackup')->files()[1]
        );

        $this->assertNotEmpty($value);
    }

    /** @test **/
    public function it_remove_config_file_after_backup_is_done(): void
    {
        $this->artisan('db:backup');

        $this->assertFalse(Storage::disk('dbbackup')->exists('mysqldump.cnf'));
    }

}
