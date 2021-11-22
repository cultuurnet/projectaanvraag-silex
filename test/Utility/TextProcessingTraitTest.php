<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Utility;

use PHPUnit\Framework\TestCase;

class TextProcessingTraitTest extends TestCase
{
    use TextProcessingTrait;

    /**
     * @test
     */
    public function it_does_not_trim_closing_html_tags(): void
    {
        // Use an already transformed description to simulate the creation of a summary from a description.
        $description = 'À la périphérie bruxelloise, le parc déploie ses pelouses, ses bois, ses arbres remarquables, ses massifs de rhododendrons,... plus de 450 espèces de plantes sauvages sont recensées sur le site.<br />
<br />
Partez à la découverte de ce site majestueux, un casque sur les oreilles, en écoutant les audioguides avec votre smartphone.<br />
<br />
Les commentaires sont adaptés aux amateurs de jardin, mais aussi aux enfants.<br />';

        $this->assertEquals(
            'À la périphérie bruxelloise, le parc déploie ses pelouses, ses bois, ses arbres remarquables, ses massifs de rhododendrons,... plus de 450 espèces de plantes sauvages sont recensées sur le site.<br />',
            $this->createHtmlSummary($description, 200)
        );
    }
}
