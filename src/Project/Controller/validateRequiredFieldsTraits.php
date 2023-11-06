<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;

trait validateRequiredFieldsTraits
{
    /**
     * Validate if all required fields are in the data.
     * @param \stdClass $data
     * @throws MissingRequiredFieldsException
     */
    private function validate($requiredFields, \stdClass $data = null)
    {
        $emptyFields = [];
        foreach ($requiredFields as $field) {
            if (empty($data->$field)) {
                $emptyFields[] = $field;
            }
        }

        if (!empty($emptyFields)) {
            throw new MissingRequiredFieldsException('Some required fields are missing: ' . implode(', ', $emptyFields));
        }
    }
}
