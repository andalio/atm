<?php

namespace App\Domain\Operation\Handler;

use App\Domain\Commission\Calculator;
use App\Domain\Operation\Exception\FileIsEmptyException;
use App\Domain\Operation\Exception\FileNotFoundException;
use App\Infrastructure\Csv\CsvFileReader;

class ProcessCsvHandler
{
    public function __construct(
        private readonly CsvFileReader $csvFileReader,
        private readonly Calculator $calculator
    ) {
    }

    public function handleCsvProcessing(string $filePath): array
    {
        $commissions = [];

        if (!file_exists($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $operations = $this->csvFileReader->read($filePath);

        if (empty($operations)) {
            throw new FileIsEmptyException();
        }

        foreach ($operations as $operation) {
            $commission = $this->calculator->calculate($operation);
            $commissions[] = [$commission, $operation->getCurrency()];
        }

        return $commissions;
    }

}