<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\Pipeline;

/**
 * Pipeline parser
 */
class PipelineParser implements ParserInterface
{
    /**
     * Parse a pipeline based on the given data
     *
     * @param mixed $data
     * @return Pipeline The parsed project.
     */
    public static function parseToResult($data)
    {
        $pipeline = new Pipeline();
        $pipeline->setId(!empty($data['PIPELINE_ID']) ? $data['PIPELINE_ID'] : null);
        $pipeline->setName(!empty($data['PIPELINE_NAME']) ? $data['PIPELINE_NAME'] : null);
        $pipeline->setForOpportunities(!empty($data['FOR_OPPORTUNITIES']) ? $data['FOR_OPPORTUNITIES'] : null);
        $pipeline->setForProjects(!empty($data['FOR_PROJECTS']) ? $data['FOR_PROJECTS'] : null);
        $pipeline->setOwnerUserId(!empty($data['OWNER_USER_ID']) ? $data['OWNER_USER_ID'] : null);

        return $pipeline;
    }
}
