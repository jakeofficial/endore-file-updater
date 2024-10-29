<?php

namespace App\Http\Controllers;

use App\Models\UpdaterFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UpdaterFilesCheckController extends Controller
{
    public function __invoke(Request $request)
    {
        $files_to_check = $request->post('files_to_check');

        Log::debug('Files to check: ', $request->post());

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
