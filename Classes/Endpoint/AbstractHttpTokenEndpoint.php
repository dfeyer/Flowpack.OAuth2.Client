<?php

namespace Flowpack\OAuth2\Client\Endpoint;

/*
 * This file is part of the Flowpack.OAuth2.Client package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\OAuth2\Client\Exception as OAuth2Exception;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Client\CurlEngineException;
use Neos\Flow\Http\Exception;
use Neos\Flow\Http\Request;
use Neos\Flow\Http\Uri;
use Neos\Utility\Arrays;

abstract class AbstractHttpTokenEndpoint implements TokenEndpointInterface
{
    /**
     * @Flow\Inject
     * @var CurlEngine
     */
    protected $requestEngine;

    /**
     * @var string
     */
    protected $endpointUri;

    /**
     * @var string
     * @see http://tools.ietf.org/html/rfc6749#section-2.2
     */
    protected $clientIdentifier;

    /**
     * @var string
     * @see http://tools.ietf.org/html/rfc6749#section-2.3.1
     */
    protected $clientSecret;

    /**
     */
    protected function initializeObject()
    {
        $this->requestEngine->setOption(CURLOPT_CAINFO, FLOW_PATH_PACKAGES . 'Application/Flowpack.OAuth2.Client/Resources/Private/cacert.pem');
        $this->requestEngine->setOption(CURLOPT_SSL_VERIFYPEER, true);
    }

    /**
     * Requests an access token for Client Credentials Grant as specified in section 4.4.2
     *
     * @param string $code The authorization code received from the authorization server.
     * @param string $redirectUri REQUIRED, if the "redirect_uri" parameter was included in the authorization request as described in Section 4.1.1, and their values MUST be identical.
     * @param string $clientIdentifier REQUIRED, if the client is not authenticating with the authorization server as described in Section 3.2.1.
     * @return mixed
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.3
     * @throws OAuth2Exception
     */
    public function requestAuthorizationCodeGrantAccessToken($code, $redirectUri = null, $clientIdentifier = null)
    {
        return $this->requestAccessToken(TokenEndpointInterface::GRANT_TYPE_AUTHORIZATION_CODE, [
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientIdentifier
        ]);
    }

    /**
     * Requests an access token for Resource Owner Password Credentials Grant as specified in section 4.3.2
     *
     * @param string $username The resource owner username.
     * @param string $password The resource owner password.
     * @param array $scope The scope of the access request as described by http://tools.ietf.org/html/rfc6749#section-3.3
     * @return mixed
     * @see http://tools.ietf.org/html/rfc6749#section-4.3.2
     */
    public function requestResourceOwnerPasswordCredentialsGrantAccessToken($username, $password, $scope = [])
    {
        // TODO: Implement requestResourceOwnerPasswordCredentialsGrantAccessToken() method.
    }

    /**
     * Requests an access token for Client Credentials Grant as specified in section 4.4.2
     *
     * @param array $scope The scope of the access request as described by http://tools.ietf.org/html/rfc6749#section-3.3
     * @return mixed
     * @see http://tools.ietf.org/html/rfc6749#section-4.4.2
     * @throws OAuth2Exception
     */
    public function requestClientCredentialsGrantAccessToken($scope = [])
    {
        return $this->requestAccessToken(TokenEndpointInterface::GRANT_TYPE_CLIENT_CREDENTIALS, $scope);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->endpointUri;
    }

    /**
     * @param string $grantType One of this' interface GRANT_TYPE_* constants
     * @param array $additionalParameters Additional parameters for the request
     * @return mixed
     * @throws \Flowpack\OAuth2\Client\Exception
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.3
     */
    protected function requestAccessToken($grantType, $additionalParameters = [])
    {
        $parameters = [
            'grant_type' => $grantType,
            'client_id' => $this->clientIdentifier,
            'client_secret' => $this->clientSecret
        ];
        $parameters = Arrays::arrayMergeRecursiveOverrule($parameters, $additionalParameters, false, false);

        $request = Request::create(new Uri($this->endpointUri), 'POST', $parameters);
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');

        try {
            $response = $this->requestEngine->sendRequest($request);
        } catch (\Exception $exception) {
            throw new OAuth2Exception('Unable to send the request', 1383749752, $exception);
        }

        if ($response->getStatusCode() !== 200) {
            throw new OAuth2Exception(sprintf('The response when requesting the access token was not as expected, code and message was: %d %s', $response->getStatusCode(), $response->getContent()), 1383749757);
        }

        // expects Tokens from Facebook or Google
        // google returns json
        // facebook an string with parameters
        parse_str($response->getContent(), $responseComponentsParsedString);
        if (!array_key_exists('access_token', $responseComponentsParsedString)) {
            $responseComponents = $response->getContent();
            $responseComponents = json_decode($responseComponents, true);
        } else {
            $responseComponents = $responseComponentsParsedString;
        }

        return $responseComponents;
    }
}
