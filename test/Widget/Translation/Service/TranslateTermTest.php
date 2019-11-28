<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\SearchV3\ValueObjects\Term;
use PHPUnit\Framework\TestCase;

class TranslateTermTest extends TestCase
{
    const FALL_BACK_LANGUAGE = 'nl';
    const TERM_ID = '1.7.11.0.0';
    const TRANSLATED_LABEL = 'translated-label';
    const TRANSLATED_DOMAIN = 'translated-domain';
    const PREFERRED_LANGUAGE = 'en';

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|TranslationRepository
     */
    private $translationRepository;

    public function setUp()
    {
        $this->translationRepository = $this->prophesize(TranslationRepository::class);
    }

    /**
     * @test
     */
    public function it_translates_to_preferred_language()
    {
        $this->translationRepository->find(self::TERM_ID, self::FALL_BACK_LANGUAGE)->willReturn([
            'label' => self::TRANSLATED_LABEL,
            'domain' => self::TRANSLATED_DOMAIN,
        ]);

        $translateTerm = new TranslateTerm(
            $this->translationRepository->reveal(),
            self::FALL_BACK_LANGUAGE
        );

        $term = $this->aTerm();

        /** @var Term $translatedTerm */
        $translatedTerm = $translateTerm->__invoke($term, self::FALL_BACK_LANGUAGE);
        $this->assertEquals($translatedTerm->getId(), self::TERM_ID);
        $this->assertEquals($translatedTerm->getLabel(), self::TRANSLATED_LABEL);
        $this->assertEquals($translatedTerm->getDomain(), self::TRANSLATED_DOMAIN);
    }

    /**
     * @test
     */
    public function it_translates_to_fallback_language_if_there_is_no_preferred_language_translation()
    {
        $this->translationRepository->find(self::TERM_ID, self::PREFERRED_LANGUAGE)->willReturn(null);
        $this->translationRepository->find(self::TERM_ID, self::PREFERRED_LANGUAGE)->shouldBeCalled();
        $this->translationRepository->find(self::TERM_ID, self::FALL_BACK_LANGUAGE)->willReturn([
            'label' => self::TRANSLATED_LABEL,
            'domain' => self::TRANSLATED_DOMAIN,
        ]);

        $translateTerm = new TranslateTerm(
            $this->translationRepository->reveal(),
            self::FALL_BACK_LANGUAGE
        );

        $term = $this->aTerm();

        /** @var Term $translatedTerm */
        $translatedTerm = $translateTerm->__invoke($term, self::PREFERRED_LANGUAGE);
        $this->assertEquals($translatedTerm->getId(), self::TERM_ID);
        $this->assertEquals($translatedTerm->getLabel(), self::TRANSLATED_LABEL);
        $this->assertEquals($translatedTerm->getDomain(), self::TRANSLATED_DOMAIN);
    }

    /**
     * @test
     */
    public function it_returns_the_same_term_if_there_is_no_translation()
    {
        $this->translationRepository->find(self::TERM_ID, self::PREFERRED_LANGUAGE)->willReturn(null);
        $this->translationRepository->find(self::TERM_ID, self::PREFERRED_LANGUAGE)->shouldBeCalled();
        $this->translationRepository->find(self::TERM_ID, self::FALL_BACK_LANGUAGE)->willReturn(null);
        $this->translationRepository->find(self::TERM_ID, self::FALL_BACK_LANGUAGE)->shouldBeCalled();

        $translateTerm = new TranslateTerm(
            $this->translationRepository->reveal(),
            self::FALL_BACK_LANGUAGE
        );

        $term = $this->aTerm();

        /** @var Term $translatedTerm */
        $translatedTerm = $translateTerm->__invoke($term, self::PREFERRED_LANGUAGE);
        $this->assertEquals($translatedTerm->getId(), self::TERM_ID);
        $this->assertEquals($translatedTerm->getLabel(), $term->getLabel());
        $this->assertEquals($translatedTerm->getDomain(), $term->getDomain());
    }

    private function aTerm(): Term
    {
        $term = new Term();
        $term->setId(self::TERM_ID);
        $term->setDomain('domain');
        $term->setLabel('label');
        return $term;
    }
}
