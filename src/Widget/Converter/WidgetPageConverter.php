<?php

namespace CultuurNet\ProjectAanvraag\Widget\Converter;

use CultuurNet\ProjectAanvraag\ConverterInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a converter for widgetPages
 */
class WidgetPageConverter implements ConverterInterface
{

    /**
     * @var DocumentRepository
     */
    protected $widgetPageRepository;

    /**
     * @var WidgetPageEntityDeserializer
     */
    protected $widgetPageEntityDeserializer;

    /**
     * WidgetPageConverter constructor.
     * @param DocumentRepository $widgetPageRepository
     * @param WidgetPageEntityDeserializer $widgetPageEntityDeserializer
     */
    public function __construct(DocumentRepository $widgetPageRepository, WidgetPageEntityDeserializer $widgetPageEntityDeserializer)
    {
        $this->widgetPageRepository = $widgetPageRepository;
        $this->widgetPageEntityDeserializer = $widgetPageEntityDeserializer;
    }

    /**
     * {@inheritdoc}
     * @return WidgetPageInterface|null
     */
    public function convert($id)
    {
        /** @var WidgetPageEntity $page */
       /* $json = $this->widgetPageRepository->findOneBy(
            [
                'id' => $id,
            ]
        );*/

        $json = file_get_contents(__DIR__ . '/../../../test/Widget/data/page.json');
        $page = $this->widgetPageEntityDeserializer->deserialize($json);


        if (empty($page)) {
            throw new NotFoundHttpException('The project was not found');
        }

        return $page;
    }

    /**
     * Convert the given id to the draft version of a page (or published if no draft exists).
     * @param $id
     *
     * @return WidgetPageEntity|null
     */
    public function convertToDraft($id)
    {
        /** @var WidgetPageEntity $page */
        $pages = $this->widgetPageRepository->findBy(
            [
                'id' => $id,
            ]
        );

        $pageToLoad = NULL;
        foreach ($pages as $page) {
            if ($page->isDraft()) {
                return $page;
            }

            $pageToLoad = $page;

        }

        if (empty($pageToLoad)) {
            throw new NotFoundHttpException('The project was not found');
        }

        return $pageToLoad;
    }
}
