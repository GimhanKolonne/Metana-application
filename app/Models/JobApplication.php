<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cv_path',
        'cv_url',
        'parsed_data',
        'status',
    ];

    protected $casts = [
        'parsed_data' => 'array',  // This ensures `parsed_data` is automatically cast to an array
    ];
}
