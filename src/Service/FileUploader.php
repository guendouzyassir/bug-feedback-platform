<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(private readonly string $targetDirectory)
    {
    }

    public function upload(UploadedFile $file): string
    {
        if (!is_dir($this->targetDirectory) && !mkdir($this->targetDirectory, 0755, true) && !is_dir($this->targetDirectory)) {
            throw new FileException('Unable to create the screenshot upload directory.');
        }

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $filename = bin2hex(random_bytes(16)).'.'.mb_strtolower($extension);

        $file->move($this->targetDirectory, $filename);

        return $filename;
    }

    public function getPath(string $filename): string
    {
        if (basename($filename) !== $filename) {
            throw new FileException('Invalid screenshot filename.');
        }

        return $this->targetDirectory.'/'.$filename;
    }

    public function remove(?string $filename): void
    {
        if ($filename === null) {
            return;
        }

        $path = $this->getPath($filename);

        if (is_file($path) && !unlink($path)) {
            throw new FileException('Unable to delete the screenshot file.');
        }
    }
}
