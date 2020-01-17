<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation;

use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;

class TranslationTwigExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private $fallBackLanguage;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(string $fallBackLanguage, TranslatorInterface $translator)
    {
        $this->fallBackLanguage = $fallBackLanguage;
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new \Twig\TwigFilter(
                'transTo',
                [
                    $this,
                    'transTo',
                ]
            ),
        ];
    }

    public function transTo(string $input, $preferredLanguage = 'nl', string $type = 'messages')
    {
        return $this->translator->trans($input, [], $type, $preferredLanguage);
    }
}
