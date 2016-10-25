<?php

namespace CultuurNet\ProjectAanvraag\ErrorHandler;

use CultuurNet\ProjectAanvraag\Core\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * JsonErrorHandler captures exceptions and formats them to the wanted json output
 */
class JsonErrorHandler
{
    /**
     * @param ValidationException $e
     * @param Request $request
     * @return null|JsonResponse
     */
    public function handleValidationExceptions(ValidationException $e, Request $request)
    {
        if ($this->shouldHandle($request)) {
            return null;
        }

        return new JsonResponse($e->getMessage(), 400);
    }

    /**
     * @param \Exception $e
     * @param Request $request
     * @return null|JsonResponse
     */
    public function handleException(\Exception $e, Request $request)
    {
        if ($this->shouldHandle($request)) {
            return null;
        }

        return new JsonResponse($e->getMessage(), 500);
    }

    /**
     * @param Request $request
     * @return null
     */
    private function shouldHandle($request)
    {
        if (!in_array('application/json', $request->getAcceptableContentTypes())) {
            return false;
        }
        return true;
    }
}
