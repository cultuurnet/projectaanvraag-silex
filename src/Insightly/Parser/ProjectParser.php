<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;

/**
 * Project parser
 */
class ProjectParser extends PrimaryEntityParser implements ParserInterface
{
    /**
     * Parse a project based on the given data
     *
     * @param mixed $data
     * @return Project The parsed project.
     */
    public static function parseToResult($data)
    {
        $project = new Project();

        self::setPrimaryData($project, $data);

        $project->setId(!empty($data['PROJECT_ID']) ? $data['PROJECT_ID'] : null);
        $project->setName(!empty($data['PROJECT_NAME']) ? $data['PROJECT_NAME'] : null);
        $project->setStatus(!empty($data['STATUS']) ? $data['STATUS'] : null);
        $project->setDetails(!empty($data['PROJECT_DETAILS']) ? $data['PROJECT_DETAILS'] : null);
        $project->setOpportunityId(!empty($data['OPPORTUNITY_ID']) ? $data['OPPORTUNITY_ID'] : null);

        $project->setStartedDate(!empty($data['STARTED_DATE']) ? \DateTime::createFromFormat('Y-m-d H:i:s', $data['STARTED_DATE']) : null);
        $project->setCompletedDate(!empty($data['COMPLETED_DATE']) ? \DateTime::createFromFormat('Y-m-d H:i:s', $data['COMPLETED_DATE']) : null);
        $project->setResponsibleUserId(!empty($data['RESPONSIBLE_USER_ID']) ? $data['RESPONSIBLE_USER_ID'] : null);

        $project->setCategoryId(!empty($data['CATEGORY_ID']) ? $data['CATEGORY_ID'] : null);
        $project->setPipelineId(!empty($data['PIPELINE_ID']) ? $data['PIPELINE_ID'] : null);
        $project->setStageId(!empty($data['STAGE_ID']) ? $data['STAGE_ID'] : null);

        return $project;
    }
}
