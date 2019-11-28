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
    public function it_returns_empty_array_if_no_values()
    {
        $result = $this->translateLanguage->__invoke([], self::PREFERRED_KEY);
        $this->assertEquals([], $result);
    }
}
