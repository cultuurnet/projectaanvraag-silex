<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Item\PrimaryEntityBase;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;

/**
 * Organisation parser
 */
class PrimaryEntityParser
{
    /**
     * Parse primary entity attributes.
     *
     * @param mixed $data
     */
    public static function setPrimaryData(PrimaryEntityBase $entity, $data)
    {

        $entity->setOwnerUserId(!empty($data['OWNER_USER_ID']) ? $data['OWNER_USER_ID'] : null);
        $entity->setImageUrl(!empty($data['IMAGE_URL']) ? $data['IMAGE_URL'] : null);
        $entity->setDateCreatedUTC(!empty($data['DATE_CREATED_UTC']) ? \DateTime::createFromFormat('Y-m-d H:i:s', $data['DATE_CREATED_UTC']) : null);
        $entity->setDateUpdatedUTC(!empty($data['DATE_UPDATED_UTC']) ? \DateTime::createFromFormat('Y-m-d H:i:s', $data['DATE_UPDATED_UTC']) : null);
        $entity->setVisibleTo(!empty($data['VISIBLE_TO']) ? $data['VISIBLE_TO'] : null);
        $entity->setVisibleTeamId(!empty($data['VISIBLE_TEAM_ID']) ? $data['VISIBLE_TEAM_ID'] : null);
        $entity->setVisibleUserIds(!empty($data['VISIBLE_USER_IDS']) ? explode(',', $data['VISIBLE_USER_IDS']) : null);

        // Parse Tags
        if (!empty($data['TAGS'])) {
            $tagList = new EntityList();
            foreach ($data['TAGS'] as $item) {
                $tagList->append(TagParser::parseToResult($item));
            }

            $entity->setTags($tagList);
        }

        // Parse Links
        if (!empty($data['LINKS'])) {
            $linkList = new EntityList();
            foreach ($data['LINKS'] as $item) {
                $linkList->append(LinkParser::parseToResult($item));
            }

            $entity->setLinks($linkList);
        }

        // Parse custom fields.
        if (!empty($data['CUSTOMFIELDS'])) {
            foreach ($data['CUSTOMFIELDS'] as $customField) {
                $entity->addCustomField($customField['CUSTOM_FIELD_ID'], $customField['FIELD_VALUE']);
            }
        }

        $entity->setCanEdit(!empty($data['CAN_EDIT']) ? $data['CAN_EDIT'] : null);
        $entity->setCanDelete(!empty($data['CAN_DELETE']) ? $data['CAN_DELETE'] : null);
    }
}
