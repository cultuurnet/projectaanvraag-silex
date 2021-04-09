<?php

namespace CultuurNet\ProjectAanvraag\ShareProxy\Converter;

use CultuurNet\ProjectAanvraag\ConverterInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\Parameter\Query;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Retrieve a offer object by its cbid.
 */
class OfferCbidConverter implements ConverterInterface
{

    /**
     * @var SearchClient
     */
    protected $searchClient;

    /**
     * OfferCbidConverter constructor.
     * @param SearchClient $searchClient
     */
    public function __construct(SearchClient $searchClient)
    {
        $this->searchClient = $searchClient;
    }

    /**
     * @return object|null
     */
    public function convert($cdbid)
    {
        // Retrieve event corresponding to ID.
        $query = new SearchQuery(true);
        $query->addParameter(
            new Query($cdbid)
        );
        // Retrieve results from Search API.
        $result = $this->searchClient->searchEvents($query);
        $items = $result->getMember()->getItems();

        if (empty($items)) {
            throw new NotFoundHttpException('No offer found for given cbid');
        }
        return $items[0];
    }
}
