<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetContactResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetContactsResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetLinksResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetOrganisationResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetPipelinesResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetProjectResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetProjectsResult;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\EntityBodyInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class InsightlyClient implements InsightlyClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $guzzleClient;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    private $responseCache = [];

    /**
     * InsightlyClient constructor.
     * @param ClientInterface $guzzleClient
     * @param $apiKey
     */
    public function __construct(ClientInterface $guzzleClient, $apiKey)
    {
        $this->guzzleClient = $guzzleClient;
        $this->apiKey = $apiKey;
    }

    /**
     * Add filters to the query
     *
     * Accepted options:
     *  - top (int)
     *  - skip (int)
     *  - brief (bool)
     *  - count_total (int)
     *
     * @param array $options
     * @return ParameterBag $query
     */
    private function addQueryFilters(array $options)
    {
        $query = new ParameterBag();

        if (!empty($options['top'])) {
            $query->add(['top' => $options['top']]);
        }

        if (!empty($options['skip'])) {
            $query->add(['skip' => $options['skip']]);
        }

        if (!empty($options['brief'])) {
            $query->add(['brief' => $options['brief'] ? 'true' : 'false']);
        }

        if (!empty($options['count_total'])) {
            $query->add(['count_total' => $options['count_total'] ? 'true' : 'false']);
        }

        if (!empty($options['email'])) {
            $query->add(['email' => $options['email']]);
        }

        return $query;
    }

    /**
     * Returns a cache key for a given request
     *
     * @param $method
     * @param $uri
     * @param ParameterBag $query
     * @return string
     */
    private function getRequestCacheKey($method, $uri, ParameterBag $query = null)
    {
        $key = $method . $uri;
        if (!empty($query)) {
            $key .= json_encode($query->all());
        }

        return md5($key);
    }

    /**
     * Send and handle a request.
     * @param string $method
     * @param string $uri
     * @param ParameterBag $query
     * @param string|resource|array|EntityBodyInterface $body
     * @return Response
     */
    private function request($method, $uri, ParameterBag $query = null, $body = null)
    {
        $cacheKey = null;
        if ($method === 'GET') {
            $cacheKey = $this->getRequestCacheKey($method, $uri, $query);
        }

        if (!$cacheKey || !isset($this->responseCache[$cacheKey])) {
            $options = [];
            if (!empty($query)) {
                $options['query'] = $query->all();
            }

            $headers = [
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
                'Content-Type' => 'application/json',
            ];

            $response = $this->guzzleClient->createRequest($method, $uri, $headers, $body, $options)->send();
            if (!$cacheKey) {
                return $response;
            } else {
                $this->responseCache[$cacheKey] = $response;
            }
        }

        return $this->responseCache[$cacheKey];
    }

    public function getProjects($options = [])
    {
        $query = $this->addQueryFilters($options);
        return GetProjectsResult::parseToResult($this->request(RequestInterface::GET, 'Projects', $query));
    }

    public function getProject($id)
    {
        return GetProjectResult::parseToResult($this->request(RequestInterface::GET, 'Projects/' . $id));
    }

    public function deleteProject($id)
    {
        $response = $this->request(RequestInterface::DELETE, 'Projects/' . $id);
        return $response->getStatusCode() === 202;
    }

    public function getContact($id)
    {
        return GetContactResult::parseToResult($this->request(RequestInterface::GET, 'Contacts/' . $id));
    }

    public function deleteContact($id)
    {
        $response = $this->request(RequestInterface::DELETE, 'Contacts/' . $id);
        return $response->getStatusCode() === 202;
    }

    public function getContactByEmail($email)
    {
        $options['top'] = 1;
        $options['email'] = $email;
        $query = $this->addQueryFilters($options);
        return GetContactsResult::parseToResult($this->request(RequestInterface::GET, 'Contacts/Search', $query));
    }

    public function updateProject($project, $options = [])
    {
        $query = $this->addQueryFilters($options);
        $updatedProject =  GetProjectResult::parseToResult($this->request(RequestInterface::PUT, 'Projects', $query, json_encode($project->toInsightly())));

        if ($project->getLinks()) {
            /** @var Link $link */
            foreach ($project->getLinks() as $link) {
                $this->request(RequestInterface::POST, 'Projects/' . $updatedProject->getId() . '/Links', $query, json_encode($link->toInsightly()));
            }
        }

        return $updatedProject;
    }

    public function createProject($project, $options = [])
    {
        $query = $this->addQueryFilters($options);
        $createdProject = GetProjectResult::parseToResult($this->request(RequestInterface::POST, 'Projects', $query, json_encode($project->toInsightly())));

        if ($project->getLinks()) {
            /** @var Link $link */
            foreach ($project->getLinks() as $link) {
                $this->request(RequestInterface::POST, 'Projects/' . $createdProject->getId() . '/Links', $query, json_encode($link->toInsightly()));
            }
        }

        return $createdProject;
    }

    public function createContact($contact)
    {
        return GetContactResult::parseToResult($this->request(RequestInterface::POST, 'Contacts', null, json_encode($contact->toInsightly())));
    }

    public function getPipelines($options = [])
    {
        $query = $this->addQueryFilters($options);
        return GetPipelinesResult::parseToResult($this->request(RequestInterface::GET, 'Pipelines', $query));
    }

    public function updateProjectPipeline($projectId, $pipelineId, $newStageId)
    {
        $data = [
            'PIPELINE_ID' => $pipelineId,
            'PIPELINE_STAGE_CHANGE' => [
                'STAGE_ID' => $newStageId,
            ],
        ];

        return GetProjectResult::parseToResult($this->request(RequestInterface::PUT, 'Projects/' . $projectId . '/Pipeline', null, json_encode($data)));
    }

    public function updateProjectPipelineStage($projectId, $newStageId)
    {
        $data = [
            'STAGE_ID' => $newStageId,
        ];

        return GetProjectResult::parseToResult($this->request(RequestInterface::PUT, 'Projects/' . $projectId . '/PipelineStage', null, json_encode($data)));
    }

    public function createOrganisation(Organisation $organisation)
    {
        return GetOrganisationResult::parseToResult($this->request(RequestInterface::POST, 'Organisations', null, json_encode($organisation->toInsightly())));
    }

    public function getOrganisation($organisationId)
    {
        return GetOrganisationResult::parseToResult($this->request(RequestInterface::GET, 'Organisations/' . $organisationId));
    }

    public function deleteOrganisation($id)
    {
        $response = $this->request(RequestInterface::DELETE, 'Organisations/' . $id);
        return $response->getStatusCode() === 202;
    }

    public function updateOrganisation(Organisation $organisation)
    {
        return GetOrganisationResult::parseToResult($this->request(RequestInterface::PUT, 'Organisations/', null, json_encode($organisation->toInsightly())));
    }

    public function getProjectLinks($projectId)
    {
        return GetLinksResult::parseToResult($this->request(RequestInterface::GET, 'Project/' . $projectId . '/Links'));
    }
}
