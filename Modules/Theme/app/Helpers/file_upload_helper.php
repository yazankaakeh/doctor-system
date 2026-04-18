<?php

function uploadFile($file, $path, $name = ''): string
{
    $name = hash('md5', $file->getClientOriginalName()).'.'.$file->getClientOriginalExtension();
    $file->move(public_path($path), $name);

    return $path.DIRECTORY_SEPARATOR.$name;
}

function deleteFile($src): void
{
    if (file_exists(public_path($src))) {
        unlink(public_path($src));
    }
}

function showFile($path, $is_file = false)
{
    if ($is_file) {
        return file_exists(public_path($path)) ? asset($path) : asset('img/404.png');
    } else {
        return $path;
    }

}
