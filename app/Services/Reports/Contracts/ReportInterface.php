<?php

namespace App\Services\Reports\Contracts;

interface ReportInterface
{
    /**
     * Retrieves the report's underlying data.
     *
     * @return \App\Services\Reports\Contracts\ReportDataInterface
     */
    public function getData(): ReportDataInterface;
}
