<?php

namespace App\Models;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trader extends Model
{
    use HasFactory;

    protected $fillable = [
        'business',
        'verified_trader',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function shops(){
        return $this->hasMany(Shop::class);
    }
}