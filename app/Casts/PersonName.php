<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * A person's name with tidy capitalization, both when read and when saved.
 *
 * A name typed entirely in capitals ("EDERLYN SALE") or entirely in lower
 * case ("ederlyn sale") reads as "Ederlyn Sale". A name with deliberate
 * mixed case ("dela Cruz", "McDonald") is left exactly as typed. Roman
 * numeral suffixes stay upper case ("Jose Rizal III").
 *
 * Reading tidies names already stored in capitals; saving stores the tidy
 * form, so the next save of an old record fixes it in the database too.
 */
class PersonName implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return is_string($value) ? static::tidy($value) : $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return is_string($value) ? static::tidy($value) : $value;
    }

    public static function tidy(string $value): string
    {
        // Mixed case was typed on purpose; nothing to fix without letters.
        $isAllUpper = mb_strtoupper($value) === $value;
        $isAllLower = mb_strtolower($value) === $value;
        if ((!$isAllUpper && !$isAllLower) || !preg_match('/\p{L}/u', $value)) {
            return $value;
        }

        $title = mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');

        // Initials ("J.P."), O'Neil / D'Souza, and roman numeral suffixes.
        $title = preg_replace_callback('/(?<=\p{L}\.)\p{Ll}/u', fn ($m) => mb_strtoupper($m[0]), $title);
        $title = preg_replace_callback("/(^|\\s)(\\p{L}['’])(\\p{Ll})/u", fn ($m) => $m[1] . $m[2] . mb_strtoupper($m[3]), $title);

        return preg_replace_callback(
            '/(?<=^|\s)(ii|iii|iv|vi|vii|viii|ix)(?=$|\s|[.,])/iu',
            fn ($m) => mb_strtoupper($m[0]),
            $title
        );
    }
}
