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
        $this->assertSame(
            [
                'reg-gent' => 'Regio Gent',
                'nis-44021' => 'Gent + deelgemeenten',
                'nis-44021-Z' => 'Gent (Gent)',
                'nis-25117D' => 'Gentinnes (Chastre)',
                'nis-44021F' => 'Gentbrugge (Gent)',
                'nis-44021D-III' => 'Desteldonk (Gent)',
                'nis-44021D-IV' => 'Oostakker (Gent)',
                'nis-44021D-I' => 'Sint-Kruis-Winkel (Gent)',
                'nis-44021E' => 'Sint-Amandsberg (Gent)',
                'nis-44021D-II' => 'Mendonk (Gent)',
                'nis-44021H' => 'Zwijnaarde (Gent)',
                'nis-44021G' => 'Ledeberg (Gent)',
                'nis-44021J-II' => 'Afsnee (Gent)',
                'nis-44021J-I' => 'Sint-Denijs-Westrem (Gent)',
                'nis-44021K' => 'Drongen (Gent)',
                'nis-44021L' => 'Mariakerke (Gent)',
                'nis-44021M' => 'Wondelgem (Gent)',
                'nis-62079C' => 'Hermalle-sous-Argenteau (Oupeye)',
                'nis-62108C' => 'Argenteau (Wezet)',
            ],
            $this->regionService->getAutocompletResults('Gent')
        );
    }

    /**
     * @test
     */
    public function it_will_find_match_the_start_of_a_city_name()
    {
        $this->assertSame(
            [
                'nis-42028' => 'Zele + deelgemeenten',
                'nis-42028A' => 'Zele (Zele)',
                'nis-71020B' => 'Zelem (Halen)',
                'nis-41027E' => 'Woubrechtegem (Herzele)',
                'nis-51017C' => 'Lahamaide (Elzele)',
                'nis-51017A' => 'Ellezelles (Elzele)',
                'nis-51017B' => 'Wodecq (Elzele)',
                'nis-51017' => 'Elzele + deelgemeenten',
                'nis-41034B' => 'Wanzele (Lede)',
                'nis-44052F' => 'Gijzenzele (Oosterzele)',
                'nis-44052E' => 'Landskouter (Oosterzele)',
                'nis-44052D' => 'Moortsele (Oosterzele)',
                'nis-44052C' => 'Scheldewindeke (Oosterzele)',
                'nis-44052A' => 'Oosterzele (Oosterzele)',
                'nis-44052B' => 'Balegem (Oosterzele)',
                'nis-44052' => 'Oosterzele + deelgemeenten',
                'nis-12030D' => 'Liezele (Puurs-Sint-Amands)',
                'nis-23023B' => 'Vollezele (Galmaarden)',
                'nis-41027G' => 'Steenhuize-Wijnhuize (Herzele)',
                'nis-41027F' => 'Sint-Antelinks (Herzele)',
                'nis-41027D' => 'Ressegem (Herzele)',
                'nis-41027B' => 'Hillegem (Herzele)',
                'nis-41027C' => 'Borsbeke (Herzele)',
                'nis-41027A' => 'Herzele (Herzele)',
                'nis-41027' => 'Herzele + deelgemeenten',
                'nis-41063D' => 'Vlierzele (Sint-Lievens-Houtem)',
                'nis-41018C' => 'Onkerzele (Geraardsbergen)',
                'nis-33011J' => 'Voormezele (Ieper)',
                'nis-36012B' => 'Dadizele (Moorslede)',
                'nis-37018B' => 'Zwevezele (Wingene)',
                'nis-31005C' => 'Dudzele (Brugge)',
                'nis-23060B' => 'Mazenzele (Opwijk)',
                'nis-41027H' => 'Sint-Lievens-Esse (Herzele)',
            ],
            $this->regionService->getAutocompletResults('Zele')
        );
    }

    /**
     * @test
     */
    public function it_will_find_provinces()
    {
        $this->assertSame(
            [
                'nis-60000' => 'Provincie Luik',
                'nis-62063' => 'Luik + deelgemeenten',
                'nis-62063-Z' => 'Liège (Luik)',
                'nis-62063E' => 'Bressoux (Luik)',
            ],
            $this->regionService->getAutocompletResults('Luik')
        );
    }

    /**
     * @test
     */
    public function it_puts_provinces_above_municipalities_with_the_same_name()
    {
        $this->assertSame(
            [
                'nis-70000' => 'Provincie Limburg',
                'nis-63046' => 'Limburg + deelgemeenten',
                'nis-63046A' => 'Limbourg (Limburg)',
                'nis-63046B' => 'Bilstain (Limburg)',
                'nis-63046C' => 'Goé (Limburg)',
                'reg-limburgse-kempen' => 'Regio Limburgse Kempen',
            ],
            $this->regionService->getAutocompletResults('Limburg')
        );
    }

    /**
     * @test
     */
    public function it_will_match_with_informal_names(): void
    {
        $this->assertSame(
            [
                'nis-24008C' => 'Molenbeek-Wersbeek (Bekkevoort)',
                'nis-21012' => 'Sint-Jans-Molenbeek + deelgemeenten',
                'nis-21012A' => 'Sint-Jans-Molenbeek (Sint-Jans-Molenbeek)',
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
