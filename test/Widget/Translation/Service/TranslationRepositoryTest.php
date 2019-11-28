<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\InvalidTranslationFileException;
use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\TranslationFileDoesNotExistException;
use PHPUnit\Framework\TestCase;

class TranslationRepositoryTest extends TestCase
{

    /**
     * @test
     */
    public function it_find_translation()
    {
        $loadTranslationFile = $this->prophesize(LoadTranslationFile::class);
        $translationFileData =
            [
                "1.7.11.0.0" => [
                    "label" => "translated-label",
                    "domain" => "translated-theme",
                ],
            ];
        $loadTranslationFile->__invoke('term', 'nl')->willReturn($translationFileData);

        $repository = new TranslationRepository(
            $loadTranslationFile->reveal(),
            'term'
        );

        $result = $repository->find('1.7.11.0.0', 'nl');

        $this->assertTrue(is_array($result));
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_translation_present_in_translation_file()
    {
        $loadTranslationFile = $this->prophesize(LoadTranslationFile::class);
        $translationFileData =
            [
                "1.7.11.0.0" => [
                    "label" => "translated-label",
                    "domain" => "translated-theme",
                ],
            ];
        $loadTranslationFile->__invoke('term', 'nl')->willReturn($translationFileData);

        $repository = new TranslationRepository(
            $loadTranslationFile->reveal(),
            'term'
        );

        $result = $repository->find('non-existing-id', 'nl');

        $this->assertTrue(is_null($result));
    }

    /**
     * @test
     */
    public function it_returns_null_if_translation_file_does_not_exist()
    {
        $loadTranslationFile = $this->prophesize(LoadTranslationFile::class);
        $loadTranslationFile->__invoke('term', 'nl')->willThrow(TranslationFileDoesNotExistException::class);

        $repository = new TranslationRepository(
            $loadTranslationFile->reveal(),
            'term'
        );

        $result = $repository->find('non-existing-id', 'nl');

        $this->assertTrue(is_null($result));
    }

    /**
     * @test
     */
    public function it_returns_null_if_translation_file_is_invalid()
    {
        $loadTranslationFile = $this->prophesize(LoadTranslationFile::class);
        $loadTranslationFile->__invoke('term', 'nl')->willThrow(InvalidTranslationFileException::class);

        $repository = new TranslationRepository(
            $loadTranslationFile->reveal(),
            'term'
        );

        $result = $repository->find('non-existing-id', 'nl');

        $this->assertTrue(is_null($result));
    }
}
