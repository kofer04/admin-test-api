<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'domain', 'path'];

    public function jobs()
    {
        return $this->hasMany(LogServiceTitanJob::class);
    }

    public function events()
    {
        return $this->hasMany(LogEvent::class);
    }
}
