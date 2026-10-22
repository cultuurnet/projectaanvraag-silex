<?php

namespace CultuurNet\ProjectAanvraag\Widget\Twig;

use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use CultuurNet\ProjectAanvraag\Uitpas\UitpasClient;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\FilterForKeyWithFallback;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\TranslateTerm;
use CultuurNet\SearchV3\ValueObjects\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class TwigPreprocessorOvernightStayTest extends TestCase
{
    /**
     * @var TwigPreprocessor
     */
    private $twigPreprocessor;

    public function setUp()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $this->twigPreprocessor = new TwigPreprocessor(
            $this->createMock(\Twig_Environment::class),
            new RequestStack(),
            $this->createMock(\CultureFeed::class),
            'https://social.example',
            $this->createMock(FilterForKeyWithFallback::class),
            $this->createMock(TranslateTerm::class),
            $translator,
            $this->createMock(CuratorenClient::class),
            $this->createMock(UitpasClient::class),
            false
        );
    }

    /**
     * @dataProvider overnightStayProvider
     */
    public function testFormatOvernightStay(?bool $eventValue, array $subEventValues, ?string $expected)
    {
        $event = $this->createEvent($eventValue);
        $event->setSubEvents(array_map([$this, 'createEvent'], $subEventValues));

        $method = new \ReflectionMethod(TwigPreprocessor::class, 'formatOvernightStay');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($this->twigPreprocessor, $event, 'nl'));
    }

    public function overnightStayProvider(): array
    {
        return [
            'with' => [true, [], 'event_overnight_stay_with'],
            'without' => [false, [], 'event_overnight_stay_without'],
            'not set' => [null, [], null],
            'partly with' => [null, [true, false, null], 'event_overnight_stay_partly_with'],
            'partly without' => [null, [false, null], 'event_overnight_stay_partly_without'],
            'sub events take precedence' => [true, [false], 'event_overnight_stay_without'],
        ];
    }

    private function createEvent(?bool $hasOvernightStay): Event
    {
        $event = new Event();
        if ($hasOvernightStay !== null) {
            $event->setHasOvernightStay($hasOvernightStay);
        }

        return $event;
    }
}
