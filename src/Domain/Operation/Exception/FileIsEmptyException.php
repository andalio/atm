<?php

namespace App\Domain\Operation\Exception;

class FileIsEmptyException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The file is empty.');
    }
}