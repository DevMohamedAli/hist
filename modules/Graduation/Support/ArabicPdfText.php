<?php

namespace Modules\Graduation\Support;

use ArPHP\I18N\Arabic;

class ArabicPdfText
{
    private Arabic $arabic;

    public function __construct()
    {
        $this->arabic = new Arabic;
    }

    public function text(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $text = (string) $value;

        if (! preg_match('/\p{Arabic}/u', $text)) {
            return $text;
        }

        return $this->arabic->utf8Glyphs($text, 200, false, true);
    }
}
