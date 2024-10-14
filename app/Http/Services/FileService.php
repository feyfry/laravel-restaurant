<?php
namespace App\Http\Services;

use Illuminate\Support\Facades\Storage;

class FileService
{
    public function upload($file, $path)
    {
        $name = $file->hashName();
        $file->storeAs($path, $name, 'public');
        return $name;
    }

    public function delete($file)
    {
        return Storage::disk('public')->delete($file);
    }
}
