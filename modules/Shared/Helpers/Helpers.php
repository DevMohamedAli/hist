<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

if (! function_exists('user')) {
    /**
     * Get the currently authenticated user.
     */
    function user(): ?Authenticatable
    {
        return Auth::user();
    }
}

if (! function_exists('arabic_to_latin')) {
    /**
     * Transliterate Arabic characters to Latin equivalents.
     */
    function arabic_to_latin(string $arabic): string
    {
        $map = [
            'ا' => 'A',
            'ب' => 'B',
            'ت' => 'T',
            'ث' => 'TH',
            'ج' => 'J',
            'ح' => 'H',
            'خ' => 'KH',
            'د' => 'D',
            'ذ' => 'DH',
            'ر' => 'R',
            'ز' => 'Z',
            'س' => 'S',
            'ش' => 'SH',
            'ص' => 'S',
            'ض' => 'D',
            'ط' => 'T',
            'ظ' => 'DH',
            'ع' => 'A',
            'غ' => 'GH',
            'ف' => 'F',
            'ق' => 'Q',
            'ك' => 'K',
            'ل' => 'L',
            'م' => 'M',
            'ن' => 'N',
            'ه' => 'H',
            'و' => 'W',
            'ي' => 'Y',
            'ة' => 'H',
            'ى' => 'A',
            'ؤ' => 'W',
            'ئ' => 'Y',
            'ء' => '',
        ];

        $result = '';
        foreach (mb_str_split($arabic) as $char) {
            $result .= $map[$char] ?? '';
        }

        return $result;
    }
}
