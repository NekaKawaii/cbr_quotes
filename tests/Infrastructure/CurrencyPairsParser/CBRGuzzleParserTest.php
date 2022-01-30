<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\CurrencyPairsParser;

use App\CurrencyPairsParser\Event\CurrencyPairReceived;
use App\Infrastructure\CurrencyPairsParser\CBRGuzzleParser;
use App\Tests\_tools\Fake\FakeGuzzleClient;
use App\Tests\_tools\Fake\FakeMessageBus;
use App\Tests\_tools\TestCase;
use App\Type\Date;
use GuzzleHttp\Client;
use Psr\Log\NullLogger;

/**
 * @psalm-suppress MissingConstructor
 */
final class CBRGuzzleParserTest extends TestCase
{
    private FakeGuzzleClient $httpClient;

    private FakeMessageBus $messageBus;

    private CBRGuzzleParser $parser;

    /**
     * It parses data and emit events about currency pairs
     */
    public function testItEmitEventWithReceivedCurrencyPair(): void
    {
        $this->httpClient->putContentOnURI('GET', '/scripts/XML_daily.asp?date_req=02/03/2002', '
            <ValCurs Date="02.03.2002" name="Foreign Currency Market">
                <Valute ID="R01235">
                    <NumCode>840</NumCode>
                    <CharCode>USD</CharCode>
                    <Nominal>1</Nominal>
                    <Name>Доллар США</Name>
                    <Value>30,9436</Value>
                </Valute>
            </ValCurs>
        ');

        $this->parser->parse(Date::create('2002-03-02'));

        // Check for emitted event about receiving info about currency pair
        $receivedEvents = $this->messageBus->findDispatched(CurrencyPairReceived::class);

        self::assertCount(1, $receivedEvents);
        self::assertInstanceOf(CurrencyPairReceived::class, $receivedEvents[0]);

        $receivedEvent = $receivedEvents[0];

        // Check payload of event
        self::assertSame('USD', $receivedEvent->base);
        self::assertSame('RUB', $receivedEvent->quote);
        self::assertSame(30.9436, $receivedEvent->amount);
        self::assertEquals(Date::create('2002-03-02'), $receivedEvent->date);
    }

    public function testItDoesNotEmitEventOnNotCorrectCBRXMLStructure(): void
    {
        $this->httpClient->putContentOnURI('GET', '/scripts/XML_daily.asp?date_req=02/03/2002', '
            <ValCurs Date="02.03.2002" name="Foreign Currency Market">
                <Valute ID="R01235">
                    <WrongContent>1</WrongContent>
                </Valute>
            </ValCurs>
        ');

        $this->parser->parse(Date::create('2002-03-02'));

        self::assertTrue($this->messageBus->isNotDispatched(CurrencyPairReceived::class));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new FakeGuzzleClient();
        $this->messageBus = new FakeMessageBus();
        $this->parser = new CBRGuzzleParser($this->httpClient, $this->messageBus, new NullLogger());
    }
}
