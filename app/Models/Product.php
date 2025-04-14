<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
        'image',
        'end_users_id',
        'status'
    ];

    public function endUser()
    {
        return $this->belongsTo(EndUser::class, 'end_users_id');
    }
}