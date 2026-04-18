<?php

namespace Modules\Core\Action;

class EnvUpdateClass
{
    public static function updateEnvSettings(array $data): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            $escaped = preg_quote("{$key}=", '/');
            $pattern = "/^{$escaped}.*$/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}=\"{$value}\"", $content);
            } else {
                $content .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($envPath, $content);
    }
}
