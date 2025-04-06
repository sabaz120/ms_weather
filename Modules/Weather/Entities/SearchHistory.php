<?php

namespace Modules\Weather\Entities;

use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    protected $fillable = [
        "user_id",
        "city",
        "country",
        "region",
    ];
    
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
