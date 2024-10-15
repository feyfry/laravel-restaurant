<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
        'urlEmbedCode'
    ];

    public static function booted() {

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
            $model->slug = Str::slug($model->title);
        });
    }
}
