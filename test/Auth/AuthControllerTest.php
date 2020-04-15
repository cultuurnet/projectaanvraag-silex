<?php

namespace CultuurNet\UiTIDProvider\Auth;

use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Auth\User;
use CultuurNet\UiTIDProvider\User\UserSessionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthControllerTest extends TestCase
{
    /**
     * @var AuthServiceInterface|MockObject
     */
    protected $authService;

    /**
     * @var AuthController
     */
    protected $controller;

    /**
     * @var string
     */
    protected $defaultDestination;

    /**
     * @var string
     */
    protected $defaultDestinationUrl;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var TokenCredentials
     */
    protected $requestToken;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var UrlGeneratorInterface|MockObject
     */
    protected $urlGenerator;

    /**
     * @var UserSessionService
     */
    protected $userSessionService;

    protected function setUp(): void
    {
        $this->authService = $this->createMock(AuthServiceInterface::class);

        $this->defaultDestination = '/default/destination';
        $this->defaultDestinationUrl = 'http://example.com' . $this->defaultDestination;
        $this->destination = 'http://destination.com/';

        $this->session = new Session(new MockArraySessionStorage());
        $this->userSessionService = new UserSessionService($this->session);

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->requestToken = new TokenCredentials('token', 'secret');

        $this->controller = new AuthController(
            $this->authService,
            $this->userSessionService,
            $this->urlGenerator,
            $this->defaultDestination
        );
    }

    /**
     * @test
     */
    public function it_redirects_when_connecting()
    {
        // Query parameters that should be passed to the callback url.
        $queryParameters = ['destination' => $this->destination];

        // Fake callback & authorisation URLs.
        $callbackUrl = 'http://callback.com/?destination=' . $this->destination;
        $authorisationUrl = 'http://authorize.com/';

        $response = $this->mockAuthorisationCall($queryParameters, $callbackUrl, $authorisationUrl);

        // Make sure we get a redirect response with the authorisation URL.
        $this->assertEquals(
            new RedirectResponse($authorisationUrl),
            $response
        );
    }

    /**
     * @test
     */
    public function it_adds_skip_confirmation_when_requested()
    {
        // Query parameters that should be passed to the callback url.
        $queryParameters = [
            'destination' => $this->destination,
            'skipConfirmation' => 'true',
        ];

        // Fake callback & authorisation URLs.
        $callbackUrl = 'http://callback.com/?destination=' . $this->destination . '&skipConfirmation=true';
        $authorisationUrl = 'http://authorize.com/';

        $this->mockAuthorisationCall($queryParameters, $callbackUrl, $authorisationUrl);
    }

    /**
     * @test
     */
    public function it_redirects_to_a_destination_after_authorisation()
    {
        $oauthVerifier = 'verification';

        // The authorisation method should get the stored request token.
        $this->authService->expects($this->any())
            ->method('getStoredRequestToken')
            ->willReturn($this->requestToken);

        // Based on the stored request token and the oauth verifier it should
        // get the user from the authentication service.
        $userId = 1;
        $tokenCredentials = new TokenCredentials('token2', 'secret2');
        $user = new User($userId, $tokenCredentials);
        $this->authService->expects($this->any())
            ->method('getAccessToken')
            ->with($this->requestToken, $oauthVerifier)
            ->willReturn($user);

        // Afterwards it should remove the stored request token.
        $this->authService->expects($this->any())
            ->method('removeStoredRequestToken');

        // Perform a fake request to the route with the query parameters.
        $query = [
            'oauth_token' => $this->requestToken->getToken(),
            'oauth_verifier' => $oauthVerifier,
            'destination' => $this->destination,
        ];
        $request = new Request($query);
        $response = $this->controller->authorize($request);

        // Make sure the response is a redirect to the destination that
        // was set in the query parameters.
        $this->assertEquals(
            new RedirectResponse($this->destination),
            $response
        );

        // Make sure that the minimal user info has been stored in the session.
        $this->assertEquals(
            $this->userSessionService->getMinimalUserInfo(),
            $user
        );

        // Perform the fake request again, but this time without destination
        // parameter in the query.
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($this->defaultDestination)
            ->willReturn($this->defaultDestinationUrl);
        $query = [
            'oauth_token' => $this->requestToken->getToken(),
            'oauth_verifier' => $oauthVerifier,
        ];
        $request = new Request($query);
        $response = $this->controller->authorize($request);

        // Make sure that the response now redirects to the default
        // destination.
        $this->assertEquals(
            new RedirectResponse($this->defaultDestinationUrl),
            $response
        );
    }

    /**
     * Mock the authorisation requests.
     */
    private function mockAuthorisationCall($queryParameters, $callbackUrl, $authorizationUrl)
    {
        // A callback url should be generated, containing the "destination"
        // query parameter.
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                AuthController::AUTHORISATION_ROUTE_NAME,
                $queryParameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($callbackUrl);

        // A request token should be generated and stored.
        $this->authService->expects($this->once())
            ->method('getRequestToken')
            ->with($callbackUrl)
            ->willReturn($this->requestToken);
        $this->authService->expects($this->once())
            ->method('storeRequestToken')
            ->with($this->requestToken);

        // An authorisation url should be generated, based on the request
        // token.
        $this->authService->expects($this->once())
            ->method('getAuthorizeUrl')
            ->with($this->requestToken)
            ->willReturn($authorizationUrl);

        // Perform a fake request to the route with the query parameters.
        $request = new Request($queryParameters);
        return $this->controller->connect($request);
    }
}
