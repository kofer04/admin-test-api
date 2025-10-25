<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogServiceTitanJob extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'market_id',
        'service_titan_job_id',
        'start',
        'end',
        'job_status'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function market()
    {
        return $this->belongsTo(Market::class);
    }
}
