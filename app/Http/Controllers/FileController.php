<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(File $file)
    {
        try {
            $path = Storage::path($file->path);

            return response()->download($path, $file->title, [
                'Content-Type' => $file->mime,
            ]);
        } catch (Throwable $e) {
            abort(404);
        }
    }
}
