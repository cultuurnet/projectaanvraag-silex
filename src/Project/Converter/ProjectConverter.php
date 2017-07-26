<?php

namespace CultuurNet\ProjectAanvraag\Project\Converter;

use CultuurNet\ProjectAanvraag\ConverterInterface;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a provider for projects
 */
class ProjectConverter implements ConverterInterface
{

    /**
     * @var EntityRepository
     */
    protected $projectRepository;

    public function __construct(EntityRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * Convert the project id to a project without enriching it.
     * @param $id
     * @return ProjectInterface|null
     */
    public function convert($id)
    {
        $project = $this->projectRepository->find($id);

        if (empty($project)) {
            throw new NotFoundHttpException('The project was not found');
        }

        return $project;
    }

}