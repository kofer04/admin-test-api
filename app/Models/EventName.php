<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventName extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'display_on_client'];

    public function logEvents()
    {
        return $this->hasMany(LogEvent::class);
    }
}
