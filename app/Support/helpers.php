<?php

if (! function_exists('format_qty')) {
    function format_qty(mixed $value): string
    {
        $number = is_numeric($value) ? (float) $value : 0;

        return number_format(floor($number), 0, ',', '.');
    }
}
