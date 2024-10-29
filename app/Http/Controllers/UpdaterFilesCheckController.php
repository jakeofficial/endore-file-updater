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
        $files_to_check = $jsonData['files_to_check'] ?? null;
//        Log::debug('Raw input: ' . $request->getContent());
//        Log::debug('Headers: ', $request->headers->all());
//        $files_to_check = $request->post('files_to_check');

        Log::debug('Files to check: ', $files_to_check);

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
