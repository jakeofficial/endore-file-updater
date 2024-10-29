<?php

namespace App\Console\Commands;

use App\Models\UpdaterFile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Illuminate\Support\Facades\File;
use ZipArchive;

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
        $latestFile = UpdaterFile::query()->latest('updated_at')->first();

        $headers = get_headers($this->url, 1);
        if (isset($headers['Last-Modified'])) {
            $this->info("Data ostatniej modyfikacji: " . Carbon::parse($headers['Last-Modified']));
        } else {
            $this->info("Nie można uzyskać daty modyfikacji.");
        }

        if ($latestFile && $latestFile->updated_at >= Carbon::parse($headers['Last-Modified'])) {
            $this->error('Chyba mamy już nowsze pliki ? ');
            exit;
        }

        $temporaryDirectory = storage_path('app');

        $this->info($temporaryDirectory);

        $path = storage_path('app/files.zip');
        Http::timeout(0)->sink($path)->get($this->url);

        $this->info('Extracting');
        $zip = new ZipArchive();
        if ($zip->open($path) === TRUE) {
            $zip->extractTo($temporaryDirectory . '/public');
            $zip->close();
        }

        $this->info('Extracted');
        $paczkaDirectory = $temporaryDirectory . '/public/Paczka_EME/';
        $files = File::allFiles($paczkaDirectory);

        foreach ($files as $file) {
            $fullPath = $file->getPathname();
            $relativePath = str_replace($paczkaDirectory, '', $fullPath);
            $checksum = md5_file($fullPath);

            $this->info($relativePath . ' => ' . $checksum);

            UpdaterFile::query()->updateOrCreate([
                'name' => $relativePath,
                'hash' => $checksum
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

}
