<?php

namespace App\Application\Command;

use App\Domain\Operation\Handler\ProcessCsvHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: "atm:processCsv",
    description: "Process Csv file and calculate commission fees."
)]
class ProcessCsv extends Command
{
    const FILE = 'file';

    public function __construct(
        private readonly ProcessCsvHandler $processCsvHandler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::FILE, InputArgument::REQUIRED, 'Path to the Csv file');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument(self::FILE);

        $commissions = $this->processCsvHandler->handleCsvProcessing($filePath);

        $output->writeln("Commissions:\n");

        foreach ($commissions as $commissionData) {
            [$commission, $currency] = $commissionData;

            $decimalPlaces = ($currency === 'JPY') ? 0 : 2;
            $formattedCommission = number_format($commission, $decimalPlaces, '.', '');

            $output->writeln($formattedCommission);
        }

        return Command::SUCCESS;
    }
}