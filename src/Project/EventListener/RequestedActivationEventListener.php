<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\Item\Address;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Project\Event\RequestedActivation;

final class RequestedActivationEventListener extends ProjectCrudEventListener
{
    public function handle(RequestedActivation $requestedActivation): void
    {
        if ($this->newInsightlyInstance) {
            return;
        }

        $project = $requestedActivation->getProject();

        // @todo: this shouldn't fail if $project->getInsightlyProjectId() doesn't return a value, instead try creating the project again -> PROJ-43
        if (!empty($project->getInsightlyProjectId())) {
            $insightlyProject = $this->insightlyClient->getProject($project->getInsightlyProjectId());

            // Update the pipeline stage.
            $this->insightlyClient->updateProjectPipelineStage(
                $project->getInsightlyProjectId(),
                $this->insightlyConfig['stages']['aanvraag']
            );

            // Create an organisation. (We can't search on VAT, so always create a new)
            $organisation = new Organisation();
            $organisation->setName($requestedActivation->getName());

            // @todo The field is currently not known for test, ask Reinout for real field name.
            if ($requestedActivation->getVatNumber() && !empty($this->insightlyConfig['custom_fields']['vat'])) {
                $organisation->addCustomField($this->insightlyConfig['custom_fields']['vat'], $requestedActivation->getVatNumber());
            }

            if ($requestedActivation->getEmail() && !empty($this->insightlyConfig['custom_fields']['payment'])) {
                $organisation->addCustomField($this->insightlyConfig['custom_fields']['payment'], $requestedActivation->getEmail());
            }

            // Address.
            $givenAddress = $requestedActivation->getAddress();
            $address = new Address();
            $address->setType('WORK');
            $address->setStreet($givenAddress->getStreet());
            $address->setCity($givenAddress->getCity());
            $address->setPostal($givenAddress->getPostal());
            $organisation->getAddresses()->append($address);

            // Save organisation
            $organisation = $this->insightlyClient->createOrganisation($organisation);

            // Save the link organisation > project.
            $links = $insightlyProject->getLinks();
            $hasOrganisationLink = false;
            // Check if existing organisation needs an update.
            foreach ($links as $link) {
                if ($link->getOrganisationId()) {
                    $link->setOrganisationId($organisation->getId());
                    $hasOrganisationLink = true;
                }
            }

            // No existing organisation id. Create a new.
            if (!$hasOrganisationLink) {
                $link = new Link();
                $link->setOrganisationId($organisation->getId());
                $links->append($link);
            }

            $insightlyProject->setLinks($links);

            $this->insightlyClient->updateProject($insightlyProject, ['brief' => false]);
        }
    }
}
