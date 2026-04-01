<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    public $timestamps = false; // kita pakai created_at manual
    protected $fillable = ['filename', 'size', 'created_at'];
}
