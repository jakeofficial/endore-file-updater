<?php

namespace App\Http\Controllers;

use App\Models\UpdaterFile;
use Illuminate\Http\Request;

class UpdaterFilesCheckController extends Controller
{
    public function __invoke(Request $request)
    {
        $files_to_check = $request->post('files_to_check');
        $latestFiles = UpdaterFile::pluck('hash', 'name')->toArray();

        var_dump($files_to_check);die;


        foreach ($latestFiles as $name => $hash) {



            if (!isset($files_to_check[$name])) {
                $missing_or_different_files[$name] = $hash;
            } elseif ($files_to_check[$name] !== $hash) {
                $missing_or_different_files[$name] = $hash;
            }
        }

        return response()->json($missing_or_different_files);
    }
}
