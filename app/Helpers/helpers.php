<?php

use Carbon\Carbon;

if (!function_exists('getFinancialYearDates')) {
    function getFinancialYearDates(): ?array
    {
        $fy = session('financial_year');

        if (!$fy) {
            $fy = 'current';
            session(['financial_year' => $fy]);
        }

        if ($fy === 'all') {
            return null;
        }

        // Handle "current" option - convert to actual current FY
        if ($fy === 'current') {
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $fy = $currentMonth >= 4
                ? ($currentYear . '-' . substr($currentYear + 1, 2))
                : (($currentYear - 1) . '-' . substr($currentYear, 2));
        }

       
        $startYear = (int) substr($fy, 0, 4);
        $endYear = (int) ('20' . substr($fy, 5, 2));

        return [
            'start' => Carbon::create($startYear, 4, 1)->startOfDay(),
            'end' => Carbon::create($endYear, 3, 31)->endOfDay(),
        ];
    }
}

if (!function_exists('applyFinancialYearFilter')) {
    function applyFinancialYearFilter($query, string $dateColumn = 'created_at')
    {
        $fyDates = getFinancialYearDates();

        if ($fyDates) {
            $query->whereBetween($dateColumn, [$fyDates['start'], $fyDates['end']]);
        }

        return $query;
    }
}

if (!function_exists('formatIndianCurrency')) {
    function formatIndianCurrency($number, $decimals = 0): string
    {
        if ($number === null || $number === '') {
            return '0';
        }

        $number = (float) $number;
        $isNegative = $number < 0;
        $number = abs($number);

        $parts = explode('.', number_format($number, $decimals, '.', ''));
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? '.' . $parts[1] : '';

        $length = strlen($integerPart);

        if ($length <= 3) {
            $result = $integerPart;
        } else {
            $result = substr($integerPart, -3);
            $remaining = substr($integerPart, 0, $length - 3);

            while (strlen($remaining) > 2) {
                $result = substr($remaining, -2) . ',' . $result;
                $remaining = substr($remaining, 0, strlen($remaining) - 2);
            }

            if (strlen($remaining) > 0) {
                $result = $remaining . ',' . $result;
            }
        }

        return ($isNegative ? '-' : '') . $result . $decimalPart;
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney($amount, $decimals = 0): string
    {
        if ($amount === null || $amount === '') {
            return '-';
        }
        return '₹' . formatIndianCurrency($amount, $decimals);
    }
}

if (!function_exists('numberToWords')) {
    function numberToWords($number): string
    {
        $number = (int) $number;

        if ($number === 0) {
            return 'zero';
        }

        $ones = [
            0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
            6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
            11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
        ];
        $tens = [
            2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty',
            6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety',
        ];

        // Converts a value in the range 0-999 into words.
        $twoOrThree = function ($n) use ($ones, $tens) {
            $words = '';
            if ($n >= 100) {
                $words .= $ones[intdiv($n, 100)] . ' hundred';
                $n %= 100;
                if ($n > 0) {
                    $words .= ' ';
                }
            }
            if ($n > 0) {
                if ($n < 20) {
                    $words .= $ones[$n];
                } else {
                    $words .= $tens[intdiv($n, 10)];
                    if ($n % 10 > 0) {
                        $words .= ' ' . $ones[$n % 10];
                    }
                }
            }
            return $words;
        };

        $isNegative = $number < 0;
        $number = abs($number);

        $parts = [];

        $crore = intdiv($number, 10000000);
        $number %= 10000000;
        $lakh = intdiv($number, 100000);
        $number %= 100000;
        $thousand = intdiv($number, 1000);
        $number %= 1000;
        $hundred = $number;

        if ($crore > 0) {
            $parts[] = $twoOrThree($crore) . ' crore';
        }
        if ($lakh > 0) {
            $parts[] = $twoOrThree($lakh) . ' lakh';
        }
        if ($thousand > 0) {
            $parts[] = $twoOrThree($thousand) . ' thousand';
        }
        if ($hundred > 0) {
            $parts[] = $twoOrThree($hundred);
        }

        return ($isNegative ? 'minus ' : '') . implode(' ', $parts);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $withTime = false): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        if ($withTime) {
            return $date->format('d-m-Y, h:i A');
        }

        return $date->format('d-m-Y');
    }
}
