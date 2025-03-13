<?php

namespace App\Domain\Operation\Exception;

class FileCouldNotBeOpenedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The file could not be opened.');
    }
}