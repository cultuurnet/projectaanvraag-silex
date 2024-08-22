<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;

final class RegionServiceTest extends TestCase
{
    private RegionService $regionService;

    public function setUp(): void
    {
        $this->regionService = new RegionService(
            './test/Widget/regions_sample.json',
            new Translator('nl')
        );
    }

    /**
     * @test
     */
    public function it_will_show_submunicipalities(): void
    {
        $this->assertEquals(
            [
                'nis-25117D' => 'Gentinnes (Chastre)',
                'nis-44021' => 'Gent + deelgemeenten',
                'nis-44021-Z' => 'Gent (Gent)',
                'nis-44021D-II' => 'Mendonk (Gent)',
                'nis-44021D-III' => 'Desteldonk (Gent)',
                'nis-44021D-IV' => 'Oostakker (Gent)',
                'nis-44021D-I' => 'Sint-Kruis-Winkel (Gent)',
                'nis-44021E' => 'Sint-Amandsberg (Gent)',
                'nis-44021F' => 'Gentbrugge (Gent)',
                'nis-44021H' => 'Zwijnaarde (Gent)',
                'nis-44021G' => 'Ledeberg (Gent)',
                'nis-44021J-II' => 'Afsnee (Gent)',
                'nis-44021J-I' => 'Sint-Denijs-Westrem (Gent)',
                'nis-44021K' => 'Drongen (Gent)',
                'nis-44021L' => 'Mariakerke (Gent)',
                'nis-44021M' => 'Wondelgem (Gent)',
            ],
            $this->regionService->getAutocompletResults('Gent')
        );
    }

    /**
     * @test
     */
    public function it_will_find_match_the_start_of_a_city_name()
    {
        $this->assertEquals(
            [
                'nis-42028' => 'Zele + deelgemeenten',
                'nis-42028A' => 'Zele (Zele)',
                'nis-71020B' => 'Zelem (Halen)',
            ],
            $this->regionService->getAutocompletResults('Zele')
        );
    }

    /**
     * @test
     */
    public function it_does_not_match_with_informal_names(): void
    {
        $this->assertEquals(
            [
                'nis-24008C' => 'Molenbeek-Wersbeek (Bekkevoort)',
            ],
            $this->regionService->getAutocompletResults('Molenbeek')
        );
    }

    /**
     * @test
     */
    public function it_can_get_an_item_by_name(): void
    {
        $this->assertEquals(
            (object) [
                'name' => 'Liezele (Puurs-Sint-Amands)',
                'key' => 'nis-12030D',
            ],
            $this->regionService->getItemByName('Liezele (Puurs-Sint-Amands)')
        );
    }

    /**
     * @test
     */
    public function it_will_return_null_if_a_name_does_not_match(): void
    {
        $this->assertEquals(
            null,
            $this->regionService->getItemByName('Liezele')
        );
    }
}
