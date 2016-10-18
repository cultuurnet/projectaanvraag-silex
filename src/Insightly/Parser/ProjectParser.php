<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;

/**
 * Project parser
 */
class ProjectParser implements ParserInterface
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

    $project->setId(!empty($data['PROJECT_ID']) ? $data['PROJECT_ID'] : null);
    $project->setLabel(!empty($data['PROJECT_NAME']) ? $data['PROJECT_NAME'] : null);
    $project->setStatus(!empty($data['STATUS']) ? $data['STATUS'] : null);
    $project->setDetails(!empty($data['PROJECT_DETAILS']) ? $data['PROJECT_DETAILS'] : null);
    $project->setOpportunityId(!empty($data['OPPORTUNITY_ID']) ? $data['OPPORTUNITY_ID'] : null);

    $project->setStartedDate(!empty($data['STARTED_DATE']) ? \DateTime::createFromFormat('Y-m-d', $data['OPPORTUNITY_ID']) : null);
    $project->setCompletedDate(!empty($data['COMPLETED_DATE']) ? \DateTime::createFromFormat('Y-m-d', $data['COMPLETED_DATE']) : null);

    $project->setImageUrl(!empty($data['IMAGE_URL']) ? $data['IMAGE_URL'] : null);
    $project->setResponsibleUserId(!empty($data['RESPONSIBLE_USER_ID']) ? $data['RESPONSIBLE_USER_ID'] : null);
    $project->setOwnerUserId(!empty($data['OWNER_USER_ID']) ? $data['OWNER_USER_ID'] : null);

    $project->setDateCreatedUTC(!empty($data['DATE_CREATED_UTC']) ? \DateTime::createFromFormat('Y-m-d H:i:s', $data['DATE_CREATED_UTC']) : null);
    $project->setDateCreatedUTC(!empty($data['DATE_UPDATED_UTC']) ? \DateTime::createFromFormat('Y-m-d H:i:s', $data['DATE_UPDATED_UTC']) : null);

    $project->setCategoryId(!empty($data['CATEGORY_ID']) ? $data['CATEGORY_ID'] : null);
    $project->setPipelineId(!empty($data['PIPELINE_ID']) ? $data['PIPELINE_ID'] : null);
    $project->setStageId(!empty($data['STAGE_ID']) ? $data['STAGE_ID'] : null);

    $project->setVisibleTo(!empty($data['VISIBLE_TO']) ? $data['VISIBLE_TO'] : null);
    $project->setVisibleTeamId(!empty($data['VISIBLE_TEAM_ID']) ? $data['VISIBLE_TEAM_ID'] : null);
    $project->setVisibleUserIds(!empty($data['VISIBLE_USER_IDS']) ? explode(',', $data['VISIBLE_TO']) : null);

    // Parse Tags
    if (!empty($data['TAGS'])) {
      $tag_list = new EntityList();
      foreach ($data['TAGS'] as $item) {
        $tag_list->append(TagParser::parseToResult($item));
      }

      $project->setTags($tag_list);
    }

    // Parse Links
    if (!empty($data['LINKS'])) {
      $link_list = new EntityList();
      foreach ($data['LINKS'] as $item) {
        $link_list->append(LinkParser::parseToResult($item));
      }

      $project->setLinks($link_list);
    }

    $project->setCanEdit(!empty($data['CAN_EDIT']) ? $data['CAN_EDIT'] : null);
    $project->setCanDelete(!empty($data['CAN_DELETE']) ? $data['CAN_DELETE'] : null);

    return $project;
  }
}
