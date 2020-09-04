<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\SearchV3\ValueObjects\Term;
use Symfony\Component\Translation\TranslatorInterface;

class TranslateTerm
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function __invoke(Term $term, $preferredLanguage)
    {
        $translation = $this->translator->trans($term->getId(), [], $term->getDomain(), $preferredLanguage);
        // fallback logic -> return the original label if no translation
        // if found (translator returns input it was given)
        if ($translation === $term->getId()) {
            return $term->getLabel();
        }

        return $translation;
    }
}
