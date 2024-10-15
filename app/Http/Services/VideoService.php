<?php

namespace App\Http\Services;

use App\Models\Video;

class VideoService
{
    public function create($data)
    {
        return Video::create($data);
    }

    public function selectFirstBy($column, $value){
        return Video::where($column, $value)->firstOrFail();
    }

    public function update($uuid, $data){
        return Video::where('uuid', $uuid)->update($data);
    }

    public function delete($uuid){
        return Video::where('uuid', $uuid)->firstOrFail()->delete();
    }
}
