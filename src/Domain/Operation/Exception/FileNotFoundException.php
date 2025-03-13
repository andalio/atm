<?php

namespace App\Domain\Operation\Exception;

class FileNotFoundException extends \Exception
{
    public function __construct(string $filePath)
    {
        parent::__construct('File not found: ' . $filePath);
    }
}