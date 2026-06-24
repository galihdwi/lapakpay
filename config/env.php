<?php

if (!function_exists('app_load_env')) {
    function app_load_env(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        $loaded = true;
        $file = dirname(__DIR__) . '/.env';
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name === '') {
                continue;
            }

            if (
                strlen($value) >= 2
                && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (getenv($name) === false && !isset($_ENV[$name]) && !isset($_SERVER[$name])) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

if (!function_exists('app_env')) {
    function app_env(string $name, string $default = ''): string
    {
        app_load_env();

        foreach ([getenv($name), $_ENV[$name] ?? null, $_SERVER[$name] ?? null] as $value) {
            if ($value !== false && $value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return trim($default);
    }
}

if (!function_exists('app_env_bool')) {
    function app_env_bool(string $name, bool $default = false): bool
    {
        $value = strtolower(app_env($name, $default ? 'true' : 'false'));

        if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
    }
}

app_load_env();
