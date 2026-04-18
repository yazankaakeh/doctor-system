<?php

namespace Modules\Core\app\Helpers;

class FileUploadHelper
{
    public static function uploadFile($file, $path, $name = ''): string
    {
        $name = hash('md5', $file->getClientOriginalName()).'.'.$file->getClientOriginalExtension();
        $file->move(public_path($path), $name);

        return $path.DIRECTORY_SEPARATOR.$name;
    }

    public static function deleteFile($src): void
    {
        if (file_exists(public_path($src))) {
            unlink(public_path($src));
        }
    }

    public static function showFile($path, $is_file = false)
    {
        if ($is_file) {
            return file_exists(public_path($path)) ? asset($path) : asset('img/404.png');
        } else {
            return $path;
        }
    }

    public static function wa_link($raw, $defaultCc = '90'): string
    {
        $d = self::digits_only($raw);
        if (str_starts_with($d, '00')) {
            $d = substr($d, 2);
        } // 00xx => xx
        if (str_starts_with($d, '0')) {
            $d = $defaultCc.substr($d, 1);
        } // 0xxx => 90xxx

        // إذا الرقم أصلاً دولي (يبدأ بـ 90 أو 964 ...)، يمر كما هو
        return 'https://wa.me/'.$d;
    }

    public static function digits_only($s): array|string|null
    {
        return preg_replace('/\D+/', '', (string) $s);
    }
}
