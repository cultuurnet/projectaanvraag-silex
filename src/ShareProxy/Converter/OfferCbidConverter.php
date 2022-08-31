<?php

namespace CultuurNet\ProjectAanvraag\ShareProxy\Converter;

use CultuurNet\ProjectAanvraag\ConverterInterface;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\Parameter\AddressCountry;
use CultuurNet\SearchV3\Parameter\AudienceType;
use CultuurNet\SearchV3\Parameter\AvailableTo;
use CultuurNet\SearchV3\Parameter\AvailableFrom;
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
        $query->addParameter(new Query('id:' . $cdbid));
        $query->addParameter(new AudienceType('*'));
        $query->addParameter(new AddressCountry('*'));
        $query->addParameter(AvailableTo::wildcard());
        $query->addParameter(AvailableFrom::wildcard());
        // Retrieve results from Search API.
        $result = $this->searchClient->searchEvents($query);
        $items = $result->getMember()->getItems();

        if (empty($items)) {
            throw new NotFoundHttpException('No offer found for given cbid');
        }
        return $items[0];
    }
}
