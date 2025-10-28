<?php

namespace App\Services\Reports\Enums;

enum ReportFormat: string
{
    case Csv = 'csv';
    case Json = 'json';
}
