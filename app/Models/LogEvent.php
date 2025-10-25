<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'market_id',
        'event_name_id',
        'session_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function eventName()
    {
        return $this->belongsTo(EventName::class);
    }
}
