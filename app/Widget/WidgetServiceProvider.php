<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\Widget\Converter\WidgetPageConverter;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\TranslateTerm;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\FilterForKeyWithFallback;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;
use Doctrine\Common\Cache\Cache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Provides widget related services.
 */
class WidgetServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {

        $pimple['widget_repository'] = function (Container $pimple) {
            return $pimple['mongodbodm.dm']->getRepository(WidgetPageEntity::class);
        };

        $pimple['widget_layout_discovery'] = function (Container $pimple) {
            $discovery = new LayoutDiscovery();

            if ($pimple['annotation_cache'] instanceof Cache) {
                $discovery->setCache($pimple['annotation_cache']);
            }

            $discovery->register(__DIR__ . '/../../src/Widget/WidgetLayout', 'CultuurNet\ProjectAanvraag\Widget\WidgetLayout');
            return $discovery;
        };

        $pimple['widget_layout_manager'] = function (Container $pimple) {
            return new WidgetPluginManager($pimple['widget_layout_discovery'], $pimple);
        };

        $pimple['widget_type_discovery'] = function (Container $pimple) {
            $discovery = new WidgetTypeDiscovery();

            if ($pimple['annotation_cache'] instanceof Cache) {
                $discovery->setCache($pimple['annotation_cache']);
            }

            $discovery->register(__DIR__ . '/../../src/Widget/WidgetType', 'CultuurNet\ProjectAanvraag\Widget\WidgetType');
            return $discovery;
        };

        $pimple['widget_type_manager'] = function (Container $pimple) {
            return new WidgetPluginManager($pimple['widget_type_discovery'], $pimple);
        };

        $pimple['widget_page_deserializer'] = function (Container $pimple) {
            return new WidgetPageEntityDeserializer($pimple['widget_layout_manager'], $pimple['widget_type_manager']);
        };

        $pimple['widget_page_converter'] = function (Container $pimple) {
            return new WidgetPageConverter($pimple['widget_repository'], $pimple['widget_page_deserializer']);
        };

        $pimple['widget_renderer'] = function (Container $pimple) {

            /** @var RequestContext $requestContext */
            $requestContext = $pimple['request_context'];
            $renderer = new Renderer($pimple['widget_layout_manager'], $pimple['google_tag_manager'], $pimple['project_repository'], $pimple['search_api'], $pimple['search_api_test'], $pimple['curatoren_api'], $pimple['curatoren_api_test']);
            $renderer->addSettings(['apiUrl' => $requestContext->getScheme() . '://' . $requestContext->getHost() . $requestContext->getBaseUrl() . '/widgets/api']);

            return $renderer;
        };

        $pimple['widget_twig_preprocessor'] = function (Container $pimple) {
            return new TwigPreprocessor(
                $pimple['twig'],
                $pimple['request_stack'],
                $pimple['culturefeed'],
                $pimple['config']['social_host'],
                new FilterForKeyWithFallback('nl'),
                new TranslateTerm(
                    $pimple['translator']
                ),
                $pimple['translator']
            );
        };

        $pimple['widget_region_service'] = function (Container $pimple) {
            return new RegionService($pimple['region_json_location']);
        };
    }
}
