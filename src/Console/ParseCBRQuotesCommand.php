<?php

declare(strict_types=1);

namespace App\Console;

use App\Infrastructure\CurrencyPairsParser\CBRGuzzleParser;
use App\Type\Date;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ParseCBRQuotesCommand extends Command
{
    public function __construct(private CBRGuzzleParser $parser, string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var bool $parseYesterdayQuotes */
        $parseYesterdayQuotes = $input->getOption('yesterday');

        $date = $parseYesterdayQuotes ? Date::yesterday() : Date::today();

        $this->parser->parse($date);

        return 0;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('app:parse:cbr')
            ->setDescription('Parse quotes from Central Bank of Russia')
            ->addOption('yesterday', null, InputOption::VALUE_NONE, 'Parse yesterday quotes')
        ;
    }
}
