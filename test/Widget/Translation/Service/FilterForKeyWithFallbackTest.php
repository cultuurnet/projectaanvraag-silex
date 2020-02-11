<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\ProjectAanvraag\Widget\Translation\Service\FilterForKeyWithFallback;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;
use PHPUnit\Framework\TestCase;

class FilterForKeyWithFallbackTest extends TestCase
{
    const PREFERRED_KEY = 'fr';
    const FALLBACK_KEY = 'nl';

    /**
     * @var FilterForKeyWithFallback
     */
    private $translateLanguage;

    public function setUp()
    {
        $this->translateLanguage = new FilterForKeyWithFallback(
            self::FALLBACK_KEY
        );
    }

    /**
     * @test
     */
    public function it_returns_value_for_preferred_key()
    {
        $values = [
            self::PREFERRED_KEY => 'preferred-language-translation',
            self::FALLBACK_KEY => 'fallback-language-translation',
        ];

        $result = $this->translateLanguage->__invoke($values, self::PREFERRED_KEY);
        $this->assertEquals('preferred-language-translation', $result);
    }

    /**
     * @test
     */
    public function it_returns_fallback_key_value_if_preferred_is_not_present()
    {
        $values = [
            self::FALLBACK_KEY => 'fallback-language-translation',
        ];

        $result = $this->translateLanguage->__invoke($values, self::PREFERRED_KEY);
        $this->assertEquals('fallback-language-translation', $result);
    }

    /**
     * @test
     */
    public function it_returns_main_language_array_if_preferred_and_fallback_is_not_present()
    {
        $values = [
          'fr' => 'main-language-translation',
        ];

        $result = $this->translateLanguage->__invoke($values, self::PREFERRED_KEY, 'fr');
        $this->assertEquals('main-language-translation', $result);
    }

        /**
     * @test
     */
    public function it_returns_first_available_language_if_preferred_and_fallback_and_main_language_are_not_present()
    {
        $values = [
          'en' => 'english event',
        ];
        $result = $this->translateLanguage->__invoke($values, self::PREFERRED_KEY, 'fr');
        $this->assertEquals('english event', $result);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_if_no_values()
    {
        $result = $this->translateLanguage->__invoke([], self::PREFERRED_KEY);
        $this->assertEquals([], $result);
    }

}
