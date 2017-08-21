<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;
use CultuurNet\SearchV3\ValueObjects\FacetResult;
use CultuurNet\SearchV3\ValueObjects\FacetResultItem;
use CultuurNet\SearchV3\ValueObjects\FacetResults;

/**
 * Provides the facets widget type.
 *
 * @WidgetType(
 *      id = "facets",
 *      defaultSettings = {
 *          "filters" :{
 *              "what":true,
 *              "where":true,
 *              "when":false,
 *          },
 *          "group_filters" :{
 *              "enabled":false
 *          }
 *      },
 *      allowedSettings = {
 *          "search_results":"string",
 *          "filters":{
 *              "what":"boolean",
 *              "where":"boolean",
 *              "when":"boolean"
 *          },
 *          "group_filters":"CultuurNet\ProjectAanvraag\Widget\Settings\GroupFilter"
 *      }
 * )
 */
class Facets extends WidgetTypeBase
{

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // Sample facet results.
        // @todo: These should be retrieved from the SearchResults query.
        $facetResults = new FacetResults();
        $facetResults->setFacetResults([
            new FacetResult('themes', [
                new FacetResultItem('1.40.0.0.0', [
                    'nl' => 'Erfgoed',
                ],2,[]),
            ]),
            new FacetResult('themes', [
                new FacetResultItem('1.64.0.0.0', [
                    'nl' => 'Milieu en natuur',
                ],12,[]),
            ]),
            new FacetResult('themes', [
                new FacetResultItem('1.51.14.0.0', [
                    'nl' => 'Atletiek, wandelen en fietsen',
                ],46,[]),
            ]),
            new FacetResult('themes', [
                new FacetResultItem('1.37.2.0.0', [
                    'nl' => 'Samenleving',
                ],17,[]),
            ]),
            new FacetResult('types', [
                new FacetResultItem('0.6.0.0.0', [
                    'nl' => 'Beurs',
                ],5,[]),
            ]),
            new FacetResult('types', [
                new FacetResultItem('0.7.0.0.0', [
                    'nl' => 'Begeleide uitstap of rondleiding',
                ],11,[]),
            ]),
            new FacetResult('types', [
                new FacetResultItem('0.3.1.0.0', [
                    'nl' => 'Cursus of workshop',
                ],27,[]),
            ]),
            new FacetResult('types', [
                new FacetResultItem('0.3.1.0.0', [
                    'nl' => 'Cursus of workshop',
                ],27,[]),
            ]),
            new FacetResult('regions', [
                new FacetResultItem('prv-vlaams-brabant', [
                    'nl' => 'Vlaams-Brabant',
                ],22,[]),
            ]),
            new FacetResult('regions', [
                new FacetResultItem('prv-west-vlaanderen', [
                    'nl' => 'West-Vlaanderen',
                ],31,[]),
            ]),
            new FacetResult('regions', [
                new FacetResultItem('prv-oost-vlaanderen', [
                    'nl' => 'Oost-Vlaanderen',
                ],29,[]),
            ]),
            new FacetResult('regions', [
                new FacetResultItem('prv-antwerpen', [
                    'nl' => 'Antwerpen',
                ],62,[]),
            ]),
            new FacetResult('regions', [
                new FacetResultItem('prv-limburg', [
                    'nl' => 'Limburg',
                ],17,[]),
            ]),
        ]);


        // Render twig with settings.
        return $this->twig->render(
            'widgets/facets-widget/facets-widget.html.twig',
            [
                'settings_filters' => $this->settings['filters'],
                'settings_group_filters' => $this->settings['group_filters'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id]);
    }
}
