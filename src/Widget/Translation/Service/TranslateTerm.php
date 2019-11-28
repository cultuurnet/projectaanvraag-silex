<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\SearchV3\ValueObjects\Term;

class TranslateTerm
{

    /**
     * @var string
     */
    private $translationFolder;

    /**
     * @var TranslationRepository
     */
    private $translationRepository;

    /**
     * @var string
     */
    private $fallBackLanguage;

    public function __construct(
        TranslationRepository $translationRepository,
        string $translationFolder,
        string $fallBackLanguage
    ) {
        $this->translationFolder = $translationFolder;
        $this->translationRepository = $translationRepository;
        $this->fallBackLanguage = $fallBackLanguage;
    }

    public function __invoke(Term $term, string $preferredLanguage)
    {
        $translation = $this->loadTranslation($term->getId(), $preferredLanguage);

        if ($translation === null) {
            return $term;
        }

        return $this->translateTerm($term->getId(), $translation);
    }

    private function loadTranslation(string $id, string $preferredLanguage): ?array
    {
        $translation = $this->translationRepository->find($id, $preferredLanguage);

        if ($translation === null) {
            $translation = $this->translationRepository->find($id, $this->fallBackLanguage);
        }

        return $translation;
    }

    private function translateTerm(string $id, array $translation): Term
    {
        $translatedTerm = new Term();
        $translatedTerm->setId($id);
        $translatedTerm->setLabel($translation['label']);
        $translatedTerm->setDomain($translation['domain']);
        return $translatedTerm;
    }
}
