<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\InvalidTranslationFileException;
use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\TranslationFileDoesNotExistException;

class LoadTranslationFile
{
    /**
     * @var string
     */
    private $translationFolder;

    public function __construct(string $translationFolderPath)
    {
        $this->translationFolder = $translationFolderPath;
    }

    /**
     * @param string $folderName
     * @param string $language
     * @return array
     * @throws InvalidTranslationFileException
     * @throws TranslationFileDoesNotExistException
     */
    public function __invoke(string $folderName, string $language): array
    {
        $translationPath = $this->generateTranslationPath($folderName, $language);

        return $this->readFile($translationPath);
    }


    private function generateTranslationPath(string $folderName, string $preferredLanguage)
    {
        $translationFilePath = $this->buildTranslationFilePath($folderName, $preferredLanguage);

        if (!file_exists($translationFilePath)) {
            throw TranslationFileDoesNotExistException::inFolder($this->translationFolder . '/' . $folderName);
        }

        return $translationFilePath;
    }

    protected function buildTranslationFilePath(string $folderName, string $preferredLanguage): string
    {
        return $this->translationFolder . '/' . $folderName . '/' . $preferredLanguage . '.json';
    }

    protected function readFile(string $translationFilePath): array
    {
        $formattedContent = json_decode(file_get_contents($translationFilePath), true);
        if ($formattedContent === null) {
            throw InvalidTranslationFileException::forPath($translationFilePath);
        }

        return $formattedContent;
    }
}
