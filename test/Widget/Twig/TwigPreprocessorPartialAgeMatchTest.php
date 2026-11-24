<?php

namespace CultuurNet\ProjectAanvraag\Widget\Twig;

use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use CultuurNet\ProjectAanvraag\Uitpas\UitpasClient;
use CultuurNet\ProjectAanvraag\Widget\AgeFilter\AgeFilterSelection;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\FilterForKeyWithFallback;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\TranslateTerm;
use CultuurNet\SearchV3\ValueObjects\BirthdateRange;
use CultuurNet\SearchV3\ValueObjects\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class TwigPreprocessorPartialAgeMatchTest extends TestCase
{
    /**
     * @var TwigPreprocessor
     */
    private $twigPreprocessor;

    public function setUp()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('transChoice')->willReturnCallback(
            function (string $translationKey, int $count, array $parameters) {
                return $translationKey . ' (' . $count . '): ' . $parameters['%values%'];
            }
        );

        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $constructorArguments = [
            $this->createMock(\Twig_Environment::class),
            $requestStack,
            $this->createMock(\CultureFeed::class),
            'https://social.example',
            $this->createMock(FilterForKeyWithFallback::class),
            $this->createMock(TranslateTerm::class),
            $translator,
            $this->createMock(CuratorenClient::class),
            $this->createMock(UitpasClient::class),
            false,
        ];

        $this->twigPreprocessor = $this->getMockBuilder(TwigPreprocessor::class)
            ->setConstructorArgs($constructorArguments)
            ->setMethods(['preprocessEvent'])
            ->getMock();

        $this->twigPreprocessor->method('preprocessEvent')->willReturn(['id' => 'event-id']);
    }

    /**
     * @dataProvider partialAgeMatchProvider
     */
    public function testPreprocessEventListAddsPartialAgeMatch(?AgeFilterSelection $ageFilter, Event $event, ?string $expectedLabel)
    {
        $preprocessedEvents = $this->twigPreprocessor->preprocessEventList([$event], 'nl', ['items' => []], $ageFilter);

        $this->assertSame($expectedLabel, $preprocessedEvents[0]['partial_age_match']);
    }

    public function partialAgeMatchProvider(): array
    {
        return [
            'one suitable age' => [AgeFilterSelection::fromAges([8, 12]), $this->createEventForAges('6-10'), 'event_partial_age_match_age (1): 8'],
            'separate suitable ages' => [AgeFilterSelection::fromAges([6, 10, 14]), $this->createEventForAges('6-12'), 'event_partial_age_match_age (2): 6, 10'],
            'consecutive suitable ages' => [AgeFilterSelection::fromAges([6, 7, 8, 14]), $this->createEventForAges('6-10'), 'event_partial_age_match_age (3): 6 - 8'],
            'suitable birth years' => [AgeFilterSelection::fromBirthYears([2012, 2017, 2018]), $this->createEventForBirthYears('2017-01-01', '2018-12-31'), 'event_partial_age_match_birth_year (2): 2017 - 2018'],
            'all ages suitable' => [AgeFilterSelection::fromAges([6, 8]), $this->createEventForAges('6-10'), null],
            'no age filter' => [null, $this->createEventForAges('6-10'), null],
        ];
    }

    private function createEventForAges(string $typicalAgeRange): Event
    {
        $event = new Event();
        $event->setTypicalAgeRange($typicalAgeRange);

        return $event;
    }

    private function createEventForBirthYears(string $firstBirthdate, string $lastBirthdate): Event
    {
        $event = new Event();
        $event->setBirthdateRange(new BirthdateRange($firstBirthdate, $lastBirthdate));

        return $event;
    }
}
