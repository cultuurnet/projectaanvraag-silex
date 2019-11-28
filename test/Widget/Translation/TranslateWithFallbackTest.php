<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation;

use CultuurNet\ProjectAanvraag\Widget\Translation\Service\TranslateWithFallback;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;
use PHPUnit\Framework\TestCase;

class TranslateWithFallbackTest extends TestCase
{
    const PREFERRED_LANGUAGE = 'fr';
    const FALLBACK_LANGUAGE = 'nl';

    /**
     * @var TranslateWithFallback
     */
    private $translateLanguage;

    public function setUp()
    {
        $this->translateLanguage = new TranslateWithFallback(
            self::FALLBACK_LANGUAGE
        );
    }

    /**
     * @test
     */
    public function it_translates_to_preferred_language()
    {
        $translatedString = new TranslatedString(
            [
                self::PREFERRED_LANGUAGE => 'preferred-language-translation',
                self::FALLBACK_LANGUAGE => 'fallback-language-translation',
            ]
        );

        $result = $this->translateLanguage->__invoke($translatedString, self::PREFERRED_LANGUAGE);
        $this->assertEquals('preferred-language-translation', $result);
    }

    /**
     * @test
     */
    public function it_translates_to_fallback_if_preferred_is_not_present()
    {
        $translatedString = new TranslatedString(
            [
                self::FALLBACK_LANGUAGE => 'fallback-language-translation',
            ]
        );

        $result = $this->translateLanguage->__invoke($translatedString, self::PREFERRED_LANGUAGE);
        $this->assertEquals('fallback-language-translation', $result);
    }

    /**
     * @test
     */
    public function it_returns_empty_string_if_no_language_values()
    {
        $translatedString = new TranslatedString([]);

        $result = $this->translateLanguage->__invoke($translatedString, self::PREFERRED_LANGUAGE);
        $this->assertEquals('', $result);
    }
}
