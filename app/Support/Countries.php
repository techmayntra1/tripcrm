<?php

namespace App\Support;

/**
 * Single source of truth for country names and their international dialling codes.
 *
 * Used by the lead and customer forms: the country dropdown lists the names, the
 * mobile prefix lists the dialling codes, and picking a country pre-selects its code.
 */
class Countries
{
    /** Countries the business deals with most — always shown first in both dropdowns. */
    public const PINNED = ['India', 'United Arab Emirates'];

    /** Country name => international dialling code. */
    public const ALL = [
        'Afghanistan' => '+93',
        'Albania' => '+355',
        'Algeria' => '+213',
        'Andorra' => '+376',
        'Angola' => '+244',
        'Argentina' => '+54',
        'Armenia' => '+374',
        'Aruba' => '+297',
        'Australia' => '+61',
        'Austria' => '+43',
        'Azerbaijan' => '+994',
        'Bahrain' => '+973',
        'Bangladesh' => '+880',
        'Belarus' => '+375',
        'Belgium' => '+32',
        'Belize' => '+501',
        'Benin' => '+229',
        'Bhutan' => '+975',
        'Bolivia' => '+591',
        'Bosnia and Herzegovina' => '+387',
        'Botswana' => '+267',
        'Brazil' => '+55',
        'Brunei' => '+673',
        'Bulgaria' => '+359',
        'Burkina Faso' => '+226',
        'Burundi' => '+257',
        'Cambodia' => '+855',
        'Cameroon' => '+237',
        'Canada' => '+1',
        'Cape Verde' => '+238',
        'Central African Republic' => '+236',
        'Chad' => '+235',
        'Chile' => '+56',
        'China' => '+86',
        'Colombia' => '+57',
        'Comoros' => '+269',
        'Congo - Brazzaville' => '+242',
        'Congo - Kinshasa' => '+243',
        'Cook Islands' => '+682',
        'Costa Rica' => '+506',
        "Cote d'Ivoire" => '+225',
        'Croatia' => '+385',
        'Cuba' => '+53',
        'Curacao' => '+599',
        'Cyprus' => '+357',
        'Czechia' => '+420',
        'Denmark' => '+45',
        'Djibouti' => '+253',
        'Ecuador' => '+593',
        'Egypt' => '+20',
        'El Salvador' => '+503',
        'Equatorial Guinea' => '+240',
        'Eritrea' => '+291',
        'Estonia' => '+372',
        'Eswatini' => '+268',
        'Ethiopia' => '+251',
        'Falkland Islands' => '+500',
        'Faroe Islands' => '+298',
        'Fiji' => '+679',
        'Finland' => '+358',
        'France' => '+33',
        'French Guiana' => '+594',
        'French Polynesia' => '+689',
        'Gabon' => '+241',
        'Gambia' => '+220',
        'Georgia' => '+995',
        'Germany' => '+49',
        'Ghana' => '+233',
        'Gibraltar' => '+350',
        'Greece' => '+30',
        'Greenland' => '+299',
        'Guadeloupe' => '+590',
        'Guatemala' => '+502',
        'Guinea' => '+224',
        'Guinea-Bissau' => '+245',
        'Guyana' => '+592',
        'Haiti' => '+509',
        'Honduras' => '+504',
        'Hong Kong' => '+852',
        'Hungary' => '+36',
        'Iceland' => '+354',
        'India' => '+91',
        'Indonesia' => '+62',
        'Iran' => '+98',
        'Iraq' => '+964',
        'Ireland' => '+353',
        'Israel' => '+972',
        'Italy' => '+39',
        'Japan' => '+81',
        'Jordan' => '+962',
        'Kazakhstan' => '+7',
        'Kenya' => '+254',
        'Kiribati' => '+686',
        'Kuwait' => '+965',
        'Kyrgyzstan' => '+996',
        'Laos' => '+856',
        'Latvia' => '+371',
        'Lebanon' => '+961',
        'Lesotho' => '+266',
        'Liberia' => '+231',
        'Libya' => '+218',
        'Liechtenstein' => '+423',
        'Lithuania' => '+370',
        'Luxembourg' => '+352',
        'Macao' => '+853',
        'Madagascar' => '+261',
        'Malawi' => '+265',
        'Malaysia' => '+60',
        'Maldives' => '+960',
        'Mali' => '+223',
        'Malta' => '+356',
        'Marshall Islands' => '+692',
        'Martinique' => '+596',
        'Mauritania' => '+222',
        'Mauritius' => '+230',
        'Mexico' => '+52',
        'Micronesia' => '+691',
        'Moldova' => '+373',
        'Monaco' => '+377',
        'Mongolia' => '+976',
        'Montenegro' => '+382',
        'Morocco' => '+212',
        'Mozambique' => '+258',
        'Myanmar' => '+95',
        'Namibia' => '+264',
        'Nauru' => '+674',
        'Nepal' => '+977',
        'Netherlands' => '+31',
        'New Caledonia' => '+687',
        'New Zealand' => '+64',
        'Nicaragua' => '+505',
        'Niger' => '+227',
        'Nigeria' => '+234',
        'Niue' => '+683',
        'North Korea' => '+850',
        'North Macedonia' => '+389',
        'Norway' => '+47',
        'Oman' => '+968',
        'Pakistan' => '+92',
        'Palau' => '+680',
        'Palestine' => '+970',
        'Panama' => '+507',
        'Papua New Guinea' => '+675',
        'Paraguay' => '+595',
        'Peru' => '+51',
        'Philippines' => '+63',
        'Poland' => '+48',
        'Portugal' => '+351',
        'Qatar' => '+974',
        'Reunion' => '+262',
        'Romania' => '+40',
        'Russia' => '+7',
        'Rwanda' => '+250',
        'Saint Helena' => '+290',
        'Saint Pierre and Miquelon' => '+508',
        'Samoa' => '+685',
        'San Marino' => '+378',
        'Sao Tome and Principe' => '+239',
        'Saudi Arabia' => '+966',
        'Senegal' => '+221',
        'Serbia' => '+381',
        'Seychelles' => '+248',
        'Sierra Leone' => '+232',
        'Singapore' => '+65',
        'Slovakia' => '+421',
        'Slovenia' => '+386',
        'Solomon Islands' => '+677',
        'Somalia' => '+252',
        'South Africa' => '+27',
        'South Korea' => '+82',
        'South Sudan' => '+211',
        'Spain' => '+34',
        'Sri Lanka' => '+94',
        'Sudan' => '+249',
        'Suriname' => '+597',
        'Sweden' => '+46',
        'Switzerland' => '+41',
        'Syria' => '+963',
        'Taiwan' => '+886',
        'Tajikistan' => '+992',
        'Tanzania' => '+255',
        'Thailand' => '+66',
        'Timor-Leste' => '+670',
        'Togo' => '+228',
        'Tokelau' => '+690',
        'Tonga' => '+676',
        'Tunisia' => '+216',
        'Turkey' => '+90',
        'Turkmenistan' => '+993',
        'Tuvalu' => '+688',
        'Uganda' => '+256',
        'Ukraine' => '+380',
        'United Arab Emirates' => '+971',
        'United Kingdom' => '+44',
        'United States' => '+1',
        'Uruguay' => '+598',
        'Uzbekistan' => '+998',
        'Vanuatu' => '+678',
        'Vatican City' => '+379',
        'Venezuela' => '+58',
        'Vietnam' => '+84',
        'Wallis and Futuna' => '+681',
        'Yemen' => '+967',
        'Zambia' => '+260',
        'Zimbabwe' => '+263',
    ];

    /** Country names for a country dropdown, pinned ones first then alphabetical. */
    public static function names(): array
    {
        $rest = array_values(array_diff(array_keys(self::ALL), self::PINNED));

        return array_merge(self::PINNED, $rest);
    }

    /**
     * Dialling codes for the mobile prefix, as code => label.
     * Countries sharing a code (e.g. +1, +7) are merged into one entry.
     */
    public static function dialOptions(): array
    {
        $byCode = [];
        foreach (self::names() as $name) {
            $byCode[self::ALL[$name]][] = $name;
        }

        // Code first so the value stays readable when the narrow select truncates it.
        $options = [];
        foreach ($byCode as $code => $names) {
            $shown = array_slice($names, 0, 2);
            $label = implode(' / ', $shown);
            if (count($names) > 2) {
                $label .= ' +' . (count($names) - 2);
            }
            $options[$code] = $code . ' ' . $label;
        }

        return $options;
    }

    /** Valid dialling codes, for validation. */
    public static function dialCodes(): array
    {
        return array_values(array_unique(array_values(self::ALL)));
    }
}
