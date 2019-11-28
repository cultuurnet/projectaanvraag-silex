<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\InvalidTranslationFileException;
use CultuurNet\ProjectAanvraag\Widget\Translation\Exception\TranslationFileDoesNotExistException;

class TranslationRepository
{
    /**
     * @var LoadTranslationFile
     */
    private $file;

    /**
     * @var string
     */
    private $translationFolder;


    public function __construct(LoadTranslationFile $loadTranslationFile, string $translationFolder)
    {
        $this->file = $loadTranslationFile;
        $this->translationFolder = $translationFolder;
    }


    public function find(string $id, string $language): ?array
    {
        try {
            $translations = $this->loadTranslations($language);

            if (!$this->hasTranslation($id, $translations)) {
                return null;
            }

            return $translations[$id];

        } catch (TranslationFileDoesNotExistException|InvalidTranslationFileException $exception) {
            return null;
        }
    }

    protected function hasTranslation(string $id, array $translations): bool
    {
        return isset($translations[$id]);
    }

    /**
     * @param string $language
     * @return array
     * @throws TranslationFileDoesNotExistException
     * @throws InvalidTranslationFileException
     */
    protected function loadTranslations(string $language): array
    {
        return $this->file->__invoke($this->translationFolder, $language);
    }
}
