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

if (!function_exists('currencyMap')) {
    // Region => [code, symbol]. Only India and UAE are supported.
    function currencyMap(): array
    {
        return [
            'india' => ['code' => 'INR', 'symbol' => '₹'],
            'uae' => ['code' => 'AED', 'symbol' => 'AED'],
        ];
    }
}

if (!function_exists('resolveCountry')) {
    /**
     * Resolve a region key ('india' | 'uae') from whatever context is at hand:
     * a region string, a Company/Bank, or a model that belongs to a company or bank
     * (Invoice, Quotation, Trip, Income, Expense...). Falls back to India.
     */
    function resolveCountry($context = null): string
    {
        if (is_string($context)) {
            return array_key_exists($context, currencyMap()) ? $context : 'india';
        }

        if (is_object($context)) {
            if (isset($context->country) && is_string($context->country)) {
                return resolveCountry($context->country);
            }
            foreach (['company', 'bank', 'trip', 'invoice', 'quotation'] as $relation) {
                if (method_exists($context, $relation) && $context->{$relation}) {
                    return resolveCountry($context->{$relation});
                }
            }
        }

        return 'india';
    }
}

if (!function_exists('currencySymbol')) {
    function currencySymbol($context = null): string
    {
        return currencyMap()[resolveCountry($context)]['symbol'];
    }
}

if (!function_exists('currencyCode')) {
    function currencyCode($context = null): string
    {
        return currencyMap()[resolveCountry($context)]['code'];
    }
}

if (!function_exists('formatAmount')) {
    // Number only, no symbol. Indian grouping (12,34,567) for INR, standard (1,234,567) for AED.
    function formatAmount($number, $decimals = 0, $context = null): string
    {
        if (resolveCountry($context) === 'uae') {
            return number_format((float) $number, $decimals);
        }
        return formatIndianCurrency($number, $decimals);
    }
}

if (!function_exists('formatMoney')) {
    /**
     * @param mixed $context region string, Company, Bank, or a model linked to one (see resolveCountry)
     */
    function formatMoney($amount, $decimals = 0, $context = null): string
    {
        if ($amount === null || $amount === '') {
            return '-';
        }
        $country = resolveCountry($context);
        $symbol = currencySymbol($country);
        $separator = $country === 'uae' ? ' ' : '';
        return $symbol . $separator . formatAmount($amount, $decimals, $country);
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

if (!function_exists('safeFilename')) {
    /**
     * Make a string safe to use as a download filename. Document numbers can contain
     * slashes (e.g. the series "QT/2025"), which Symfony rejects in a Content-Disposition
     * header. Separators become hyphens; other characters the OS dislikes are dropped.
     */
    function safeFilename(?string $name, string $fallback = 'document'): string
    {
        $name = str_replace(['/', '\\', ':'], '-', (string) $name);
        $name = preg_replace('/[<>"|?*\x00-\x1F]/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name, " .-");

        return $name !== '' ? $name : $fallback;
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
