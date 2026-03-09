<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearTempUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:temp-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all files';

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
     * @return int
     */
    public function handle()
    {
    $path = public_path('uploads/temp');

    if (File::exists($path)) {
        File::cleanDirectory($path);
    }

    $this->info('Temp files deleted');
}
}
