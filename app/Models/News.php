<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'judul',
        'slug',
        'konten',
        'tanggal',
        'jenis',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->judul) . '-' . Str::random(5);
            }
        });
    }

    public function getJenisTextAttribute()
    {
        return match ($this->jenis) {
            1 => 'News Ticker',
            2 => 'Informasi',
            3 => 'Masjid',
            default => '-',
        };
    }
}
