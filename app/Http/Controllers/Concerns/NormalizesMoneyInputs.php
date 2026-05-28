<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait NormalizesMoneyInputs
{
    protected function normalizeMoneyInputs(Request $request, array $keys): void
    {
        $data = $request->all();

        foreach ($keys as $key) {
            if (str_contains($key, '.*.')) {
                [$arrayKey, $field] = explode('.*.', $key, 2);

                foreach (($data[$arrayKey] ?? []) as $index => $item) {
                    $data[$arrayKey][$index][$field] = $this->normalizeMoneyValue($item[$field] ?? null);
                }

                continue;
            }

            $data[$key] = $this->normalizeMoneyValue($data[$key] ?? null);
        }

        $request->merge($data);
    }

    protected function normalizeMoneyValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeMoneyValue($item), $value);
        }

        if ($value === null || $value === '') {
            return $value;
        }

        $text = trim((string) $value);
        $text = preg_replace('/Rp|\s/i', '', $text) ?? $text;

        if (preg_match('/^\d+\.\d{1,2}$/', $text)) {
            $text = explode('.', $text)[0];
        } elseif (str_contains($text, ',')) {
            $text = explode(',', $text)[0];
        }

        $digits = preg_replace('/[^\d]/', '', $text) ?? '';

        return $digits === '' ? null : $digits;
    }
}
