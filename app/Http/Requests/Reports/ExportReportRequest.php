<?php

namespace App\Http\Requests\Reports;

class ExportReportRequest extends BaseReportRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'format' => ['required', 'string', 'in:csv'],
        ]);
    }
}
