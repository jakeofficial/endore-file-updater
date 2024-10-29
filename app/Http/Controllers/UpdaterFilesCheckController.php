<?php

namespace App\Http\Controllers;

use App\Models\UpdaterFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UpdaterFilesCheckController extends Controller
{
    public function __invoke(Request $request)
    {

        $jsonData = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON decode error: ' . json_last_error_msg());
        } else {
            Log::debug('Decoded JSON: ', $jsonData ?: []);
        }

        $cleanContent = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $request->getContent());
        $jsonData = json_decode($cleanContent, true);
        Log::debug('Decoded cleaned JSON: ', $jsonData ?: []);
//        Log::debug($jsonData);
//        $files_to_check = $jsonData['files_to_check'] ?? null;
//        Log::debug('Raw input: ' . $request->getContent());
//        Log::debug('Headers: ', $request->headers->all());
        $files_to_check = $request->post('files_to_check');

        Log::debug($files_to_check);

        $latestFiles = UpdaterFile::pluck('hash', 'name')->toArray();

        $missing_or_different_files = [];

        foreach ($latestFiles as $name => $hash) {

            if (!isset($files_to_check[$name])) {
                $missing_or_different_files[$name] = $hash;
            } elseif ($files_to_check[$name] !== $hash) {
                $missing_or_different_files[$name] = $hash;
            }
        }

        Log::debug('Missing: ', $missing_or_different_files);

        return response()->json($missing_or_different_files);
    }
}
