<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
    public $timestamps = true;
    public $incrementing = true;

    // Optional: agar tidak error saat getByKey
    public static function getValue($key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }
}