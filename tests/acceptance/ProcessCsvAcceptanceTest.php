<?php

namespace App\Tests\acceptance;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ProcessCsvAcceptanceTest extends TestCase
{
    public function testCsvProcessing(): void
    {
        $csvContent = str_replace("\n", "\r\n", <<<CSV
2014-12-31,4,private,withdraw,1200.00,EUR
2015-01-01,4,private,withdraw,1000.00,EUR
2016-01-05,4,private,withdraw,1000.00,EUR
2016-01-05,1,private,deposit,200.00,EUR
2016-01-06,2,business,withdraw,300.00,EUR
2016-01-06,1,private,withdraw,30000,JPY
2016-01-07,1,private,withdraw,1000.00,EUR
2016-01-07,1,private,withdraw,100.00,USD
2016-01-10,1,private,withdraw,100.00,EUR
2016-01-10,2,business,deposit,10000.00,EUR
2016-01-10,3,private,withdraw,1000.00,EUR
2016-02-15,1,private,withdraw,300.00,EUR
2016-02-19,5,private,withdraw,3000000,JPY
CSV);

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvContent);

        $process = new Process(['php', 'bin/console', 'atm:processCsv', $tempFile]);
        $process->run();

        unlink($tempFile);

        $expectedOutput = <<<OUTPUT
Commissions:

0.60
3.00
0.00
0.06
1.50
0
0.56
0.31
0.30
3.00
0.00
0.00
8516
OUTPUT;

        $actualOutput = str_replace("\r\n", "\n", trim($process->getOutput()));
        $expectedOutput = str_replace("\r\n", "\n", trim($expectedOutput));

        $this->assertTrue($process->isSuccessful(), "Command failed: " . $process->getErrorOutput());
        $this->assertEquals($expectedOutput, $actualOutput);
    }

}