<?php

namespace App\Http\Services;

use Illuminate\Support\Str;
use App\Models\Gallery\Image;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function select($paginate = null){
        if ($paginate) {
            return Image::latest()->paginate($paginate);
        }

        return Image::latest()->get();
    }

    public function selectFirstBy($column, $value){
        return Image::where($column, $value)->firstOrFail();
    }

    public function create($data)
    {
        return Image::create($data);
    }

    public function update($uuid, $data)
    {
        return Image::where('uuid', $uuid)->update($data);
    }
}
