<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\InvalidTranslationFileException;
use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\TranslationFileDoesNotExistException;

use PHPUnit\Framework\TestCase;

class LoadTranslationFileTest extends TestCase
{
    const TRANSLATION_DIR_PATH = __DIR__ . '/../../data/translations';

    /**
     * @test
     */
    public function it_loads_translation_file()
    {
        $translationFileLoader = new LoadTranslationFile(self::TRANSLATION_DIR_PATH);
        $result = $translationFileLoader->__invoke('file_present_example', 'en');
        $this->assertTrue(is_array($result));
        $this->assertEquals('en', $result['file']);
    }

    /**
     * @test
     */
    public function it_throws_exception_if_invalid_translation_file_content()
    {
        $translationFileLoader = new LoadTranslationFile(self::TRANSLATION_DIR_PATH);
        $this->setExpectedException(InvalidTranslationFileException::class);
        $translationFileLoader->__invoke('invalid_translation_file', 'nl');
    }

    /**
     * @test
     */
    public function it_throws_exception_for_no_translation_files()
    {
        $translationFileLoader = new LoadTranslationFile(self::TRANSLATION_DIR_PATH);
        $this->setExpectedException(TranslationFileDoesNotExistException::class);
        $translationFileLoader->__invoke('no_translation_files_example', 'en');
    }
}
