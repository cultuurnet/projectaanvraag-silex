<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\MongoDB\Connection;

/**
 * Provides a controller to render widget pages and widgets.
 */
class WidgetController
{

    /**
     * @var RendererInterface
     */
    protected $renderer;

    public function __construct(RendererInterface $renderer, ObjectManager $dm, Connection $db)
    {

        $collection = $db->selectCollection('widgets', 'WidgetPage');
        $results = $collection->find();

        foreach($results as $document) {
            var_dump($document);
        }

        $test = $dm->find(WidgetPageEntity::class, 1);
        print_r($test);die();

        $page = new WidgetPageEntity();
        $page->setTitle('test');
        $page->setBody('body');

        $dm->persist($page);
        $dm->flush();

        $this->renderer = $renderer;
    }

    public function renderPage() {

    }

    public function renderWidget() {

    }

}