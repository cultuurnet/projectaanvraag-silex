<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\InvalidTranslationFileException;
use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\TranslationFileDoesNotExistException;

use PHPUnit\Framework\TestCase;

class LoadTranslationFileTest extends TestCase
{
    const TRANSLATION_DIR_PATH = __DIR__ . '/../../data/translations';
    const FALL_BACK_LANGUAGE = 'nl';

    /**
     * @test
     */
    public function it_load_preferred_language_file()
    {
        $translationFileLoader = new LoadTranslationFile(self::TRANSLATION_DIR_PATH, self::FALL_BACK_LANGUAGE);
        $result = $translationFileLoader->load('example_1', 'en');
        $this->assertTrue(is_array($result));
        $this->assertEquals('en', $result['file']);
    }

    /**
     * @test
     */
    public function it_loads_fall_back_language_file_if_preferred_not_present()
    {
        $translationFileLoader = new LoadTranslationFile(self::TRANSLATION_DIR_PATH, self::FALL_BACK_LANGUAGE);
        $result = $translationFileLoader->load('no_preferred_example', 'en');
        $this->assertTrue(is_array($result));
        $this->assertEquals('nl', $result['file']);
    }

    /**
     * @test
     */
    public function it_throws_exception_if_invalid_translation_file_content()
    {
        $translationFileLoader = new LoadTranslationFile(self::TRANSLATION_DIR_PATH, self::FALL_BACK_LANGUAGE);
        $this->setExpectedException(InvalidTranslationFileException::class);
        $translationFileLoader->load('invalid_translation_file', 'en');
    }

    /**
     * @test
     */
    public function it_throws_exception_for_no_translation_files()
    {
        $translationFileLoader = new LoadTranslationFile(self::TRANSLATION_DIR_PATH, self::FALL_BACK_LANGUAGE);
        $this->setExpectedException(TranslationFileDoesNotExistException::class);
        $translationFileLoader->load('no_translation_files_example', 'en');
    }
}
