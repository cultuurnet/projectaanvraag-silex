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
        /*$page = $this->widgetRepository->findOneBy(
            [
            'id' => '593fb8455722ed4df9064183',
            ]
        );*/

        $json = file_get_contents(__DIR__ . '/../../../test/Widget/data/page.json');
        $page = $this->widgetPageEntityDeserializer->deserialize($json);


        if (empty($page)) {
            throw new NotFoundHttpException('The project was not found');
        }

        return $page;
    }
}
