<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'domain', 'path'];

    /**
     * Users that have access to this market
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'market_user')
                    ->withTimestamps();
    }

    public function jobs()
    {
        return $this->hasMany(LogServiceTitanJob::class);
    }

    public function events()
    {
        return $this->hasMany(LogEvent::class);
    }
}
