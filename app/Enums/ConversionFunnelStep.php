<?php

namespace App\Enums;

enum ConversionFunnelStep: int
{
    case JOB_TYPE_ZIP_COMPLETED = 2;
    case APPOINTMENT_DATE_TIME_SELECTED = 3;
    case CUSTOMER_SELECTION = 7;
    case TERMS_OF_SERVICE_LOADED = 623;
    case APPOINTMENT_CONFIRMED = 8;

    /**
     * Get all steps in funnel order
     * Define the funnel flow once - all other methods derive from this
     */
    public static function allInOrder(): array
    {
        return [
            self::JOB_TYPE_ZIP_COMPLETED,
            self::APPOINTMENT_DATE_TIME_SELECTED,
            self::CUSTOMER_SELECTION,
            self::TERMS_OF_SERVICE_LOADED,
            self::APPOINTMENT_CONFIRMED,
        ];
    }

    /**
     * Get step number (1-based) - calculated from position in funnel
     */
    public function stepNumber(): int
    {
        $steps = self::allInOrder();
        $position = array_search($this, $steps, true);

        return $position !== false ? $position + 1 : 0;
    }

    /**
     * Get all event_name_id values for querying
     */
    public static function getEventNameIds(): array
    {
        return array_map(fn($step) => $step->value, self::allInOrder());
    }
}

