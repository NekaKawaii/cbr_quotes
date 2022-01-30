<?php

declare(strict_types=1);

namespace App\Infrastructure\CurrencyPairsParser;

use App\CurrencyPairsParser\CurrencyPairsParser;
use App\CurrencyPairsParser\Event\CurrencyPairReceived;
use App\Type\Date;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use function App\now;

/**
 * Central Bank of Russia parser using GuzzleHTTP library.
 */
final class CBRGuzzleParser implements CurrencyPairsParser
{
    public const CBR_BASE_URI = 'https://cbr.ru';

    public function __construct(
        private ClientInterface $client,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function parse(Date $date): void
    {
        $quotesResponse = $this->client->request(
            'GET',
            '/scripts/XML_daily.asp?date_req=' . $date->format('d/m/Y'),
            ['base_uri' => self::CBR_BASE_URI]
        );

        $quotesBody = (string)$quotesResponse->getBody();

        foreach ($this->parseQuotes($quotesBody) as $quote) {
            $this->messageBus->dispatch(new CurrencyPairReceived(
                base: $quote['code'],
                quote: 'RUB', // Central Bank of Russia has quotation for home currency only
                amount: $quote['amount'],
                date: $date,
                occurredAt: now()
            ));
        }
    }

    /**
     * Parse quotes from XML
     *
     * @psalm-return \Generator<array{code: string, amount: float}>
     */
    private function parseQuotes(string $xml): \Generator
    {
        $quotes = simplexml_load_string($xml);

        foreach ($quotes as $xmlQuote) {

            /** @var array $xmlCharCode */
            $xmlCharCode = $xmlQuote->CharCode;
            /** @var array $xmlValue */
            $xmlValue = $xmlQuote->Value;

            $code = (string)($xmlCharCode[0] ?? '');
            $amount = (float)(str_replace(',', '.', (string)($xmlValue[0] ?? '')));

            if ($code === '' || $amount === 0.0) {
                $this->logger->notice('CBRGuzzleParser: Error in quote xml', ['xml' => $xmlQuote->asXML()]);

                continue;
            }

            yield ['code' => $code, 'amount' => $amount];
        }
    }
}
