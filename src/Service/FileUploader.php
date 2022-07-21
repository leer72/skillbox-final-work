<?php

namespace App\Service;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $slugger;

    private $filesystem;

    public function __construct(FilesystemOperator $uploadsArticlesFilesystem, SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
        $this->filesystem = $uploadsArticlesFilesystem;
    }
    
    public function uploadFile(File $file, ?string $oldFileName = null): string
    {
        $fileName = $this->slugger
            ->slug(pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename(), PATHINFO_FILENAME))
            ->append('-' . uniqid())
            ->append('.', $file->guessExtension())
            ->toString()
        ;
         
        $stream = fopen($file->getPathname(), 'r');
        
        $this->filesystem->writeStream($fileName, $stream);
        
        if (is_resource($stream)) {
            fclose($stream);
        }

        if ($oldFileName && $this->filesystem->fileExists($oldFileName)) {
            $result = $this->filesystem->delete($oldFileName);
            if (!$result) {
                throw new \Exception('Не удалось удалить файл');
            }
        }

        return $fileName;
    }
}
