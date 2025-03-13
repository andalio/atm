<?php

namespace App\Infrastructure\Csv;

use App\Domain\Operation\Exception\FileCouldNotBeOpenedException;
use App\Domain\Operation\Operation;

class CsvFileReader
{
    public function read(string $filePath): array
    {
        $operations = [];

        $file = fopen($filePath, 'r');

        if ($file === false) {
            throw new FileCouldNotBeOpenedException();
        }

        while (($data = fgetcsv($file)) !== false) {
            list($date, $userId, $userType, $operationType, $amount, $currency) = $data;

            $operations[] = new Operation(
                new \DateTime($date),
                (int)$userId,
                $userType,
                $operationType,
                (float)$amount,
                $currency
            );
        }

        fclose($file);

        return $operations;
    }

}