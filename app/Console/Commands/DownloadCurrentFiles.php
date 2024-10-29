<?php

namespace App\Console\Commands;

use App\Models\UpdaterFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use ZanySoft\Zip\Zip;
use Illuminate\Support\Facades\File;

class DownloadCurrentFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-current-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected string $url = 'https://endore.pl/wp-content/uploads/Paczka_EME.zip';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $this->info($temporaryDirectory->path());

        $path = $temporaryDirectory->path('files.zip');
        Http::timeout(0)->sink($path)->get($this->url);

        $zip = Zip::open($path);
        $zip->extract($temporaryDirectory->path());
        $zip->close();


        $files = File::allFiles($temporaryDirectory->path());

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $checksum = md5_file($filePath);


            UpdaterFile::query()->where([
                'name' => $filePath,
                'hash' => $checksum
            ])->updateOrCreate([
                'updated_at' => now()
            ]);
        }


        $temporaryDirectory->delete();
    }


    private function calculateChecksums($directory)
    {
        $checksums = [];


        return $checksums;
    }
}
