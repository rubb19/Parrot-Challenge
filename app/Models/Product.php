<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    /**
     * Obtiene el usuario que es propietario del producto
     *
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
