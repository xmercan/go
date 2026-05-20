<?php

/**
 * Form validasyon helper'ları.
 * Kural tabanlı, zincirleme çağrılabilir yapıda.
 */

if (!function_exists('validate')) {
    /**
     * Veriyi kural setine göre doğrula.
     *
     * @param array $data  $_POST veya başka veri array'i
     * @param array $rules ['field' => 'required|min:3|max:255|email']
     * @return array       ['errors' => [...], 'valid' => bool]
     *
     * Örnek:
     * $result = validate($_POST, [
     *   'email'    => 'required|email|max:191',
     *   'password' => 'required|min:8',
     * ]);
     */
    function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value    = $data[$field] ?? null;
            $ruleList = explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                $error = apply_rule($field, $value, $rule, $data);
                if ($error !== null) {
                    $errors[$field][] = $error;
                    break; // Bir alan için ilk hata yeterli
                }
            }
        }

        return [
            'errors' => $errors,
            'valid'  => empty($errors),
        ];
    }
}

if (!function_exists('apply_rule')) {
    /**
     * Tek kural uygula.
     * @return string|null Hata mesajı veya null (geçerli)
     */
    function apply_rule(string $field, mixed $value, string $rule, array $allData): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        // Parametre: min:8, max:255, vb.
        [$ruleName, $param] = str_contains($rule, ':')
            ? explode(':', $rule, 2)
            : [$rule, null];

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    return "{$label} alanı zorunludur.";
                }
                break;

            case 'min':
                $len = is_string($value) ? mb_strlen($value) : (int)$value;
                if ($value !== '' && $value !== null && $len < (int)$param) {
                    return "{$label} en az {$param} karakter olmalıdır.";
                }
                break;

            case 'max':
                $len = is_string($value) ? mb_strlen($value) : (int)$value;
                if ($value !== '' && $value !== null && $len > (int)$param) {
                    return "{$label} en fazla {$param} karakter olabilir.";
                }
                break;

            case 'email':
                if ($value !== '' && $value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "Geçerli bir e-posta adresi girin.";
                }
                break;

            case 'numeric':
                if ($value !== '' && $value !== null && !is_numeric($value)) {
                    return "{$label} sayısal bir değer olmalıdır.";
                }
                break;

            case 'phone':
                // Türkiye telefon — 10-11 haneli, 0 veya +90 ile başlayabilir
                $cleaned = preg_replace('/[^0-9]/', '', (string)$value);
                if ($value !== '' && $value !== null && !preg_match('/^(0?[5][0-9]{9}|[5][0-9]{9})$/', $cleaned)) {
                    return "Geçerli bir telefon numarası girin. (Örn: 05xx xxx xxxx)";
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($allData[$confirmField] ?? '')) {
                    return "{$label} ve onay alanı eşleşmiyor.";
                }
                break;

            case 'in':
                $allowed = explode(',', $param ?? '');
                if ($value !== '' && $value !== null && !in_array($value, $allowed, true)) {
                    return "{$label} geçersiz bir değer içeriyor.";
                }
                break;

            case 'url':
                if ($value !== '' && $value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
                    return "Geçerli bir URL girin.";
                }
                break;

            case 'alpha_num':
                if ($value !== '' && $value !== null && !ctype_alnum(str_replace(['-', '_'], '', (string)$value))) {
                    return "{$label} yalnızca harf, rakam, tire veya alt çizgi içerebilir.";
                }
                break;
        }

        return null;
    }
}

if (!function_exists('old')) {
    /**
     * Validasyon hatası sonrası eski form değerini geri ver.
     * Session'da saklanmış eski input.
     */
    function old(string $field, mixed $default = ''): mixed
    {
        return $_SESSION['old_input'][$field] ?? $default;
    }
}

if (!function_exists('store_old_input')) {
    /**
     * Form hatası olduğunda inputları session'a kaydet.
     */
    function store_old_input(array $data): void
    {
        $_SESSION['old_input'] = $data;
    }
}

if (!function_exists('clear_old_input')) {
    /**
     * Eski input temizle.
     */
    function clear_old_input(): void
    {
        unset($_SESSION['old_input']);
    }
}

if (!function_exists('has_error')) {
    /**
     * Belirli bir alanda hata var mı?
     */
    function has_error(array $errors, string $field): bool
    {
        return !empty($errors[$field]);
    }
}

if (!function_exists('error_msg')) {
    /**
     * Alan hata mesajını al.
     */
    function error_msg(array $errors, string $field): string
    {
        return e($errors[$field][0] ?? '');
    }
}
