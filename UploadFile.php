<?php

namespace App\Service;

use Exception;

class UploadFile
{
    private array $files = [];

    private string $outputPath;

    public function __construct(array $files, string $outputPath): static
    {
        $this->setOutputPath($outputPath);

        $count = count($files['tmp_name']);

        for ($i = 0; $i < $count; $i++) {
            // sjdfkakfalsdf.jpg
            // File type = image/jpeg => ['image', 'jpeg']
            $fileType = explode('/', $files['type'][$i]);
            $extension = end($fileType);
            // 16cd019896f19fc76b6905bd13c3f2de . $extension
            $name = '/' . md5($files['name'][$i] . random_bytes(64)) . '.' . $extension;

            $this->files[] = [
                'name' => $name,
                'location' => $files['tmp_name'][$i],
            ];
        }

        return $this;
    }

    /**
     * Set output path
     */
    public function setOutputPath(string $path): static
    {
        $this->outputPath = $path;

        return $this;
    }

    /**
     * Save the uploaded files to disk
     */
    public function save(string $path = ''): array
    {
        // create directory if doesn't exist.
        $this->createDirectory($path);

        $uploaded = [];

        foreach ($this->files as $file) {
            $finalPathWithName = $this->outputPath . $path . $file['name'];

            move_uploaded_file($file['location'], $finalPathWithName);

            $uploaded[] = $path . $file['name'];
        }

        return $uploaded;
    }

    public function createDirectory(string $path): bool
    {
        $location = $this->outputPath . $path;

        try {
            if (! file_exists($location)) {
                mkdir($location);

                return true;
            }
        } catch (\Exception) {
            throw new Exception('Failed to create directory!');
        }
    }
}
