<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\SearchV3\ValueObjects\Term;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class TranslateTermTest extends TestCase
{
    const TERM_ID = '1.7.11.0.0';
    const PREFERRED_LANGUAGE = 'en';
    const TERM_DOMAIN = 'eventtype';
    const TERM_LABEL = 'label';

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        $this->translator = $this->prophesize(TranslatorInterface::class);
    }

    /**
     * @test
     */
    public function it_translates_to_preferred_language()
    {
        $term = $this->aTerm();

        $this->translator->trans(self::TERM_ID, [], self::TERM_DOMAIN, self::PREFERRED_LANGUAGE)
            ->willReturn('translated');

        $translateTerm = new TranslateTerm(
            $this->translator->reveal()
        );

        $translatedTerm = $translateTerm->__invoke($term, self::PREFERRED_LANGUAGE);
        $this->assertEquals($translatedTerm, 'translated');
    }

    /**
     * @test
     */
    public function it_fallback_to_returning_label_if_there_is_no_translation()
    {
        $this->translator->trans(self::TERM_ID, [], self::TERM_DOMAIN, self::PREFERRED_LANGUAGE)
            ->willReturn(self::TERM_ID);

        $translateTerm = new TranslateTerm(
            $this->translator->reveal()
        );

        $term = $this->aTerm();
        $translatedTerm = $translateTerm->__invoke($term, self::PREFERRED_LANGUAGE);
        $this->assertEquals($translatedTerm, self::TERM_LABEL);
    }

    private function aTerm(): Term
    {
        $term = new Term();
        $term->setId(self::TERM_ID);
        $term->setDomain(self::TERM_DOMAIN);
        $term->setLabel(self::TERM_LABEL);
        return $term;
    }
}
