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

use Neos\Flow\Annotations as Flow;

/**
 */
interface TokenEndpointInterface
{
    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';

    /**
     * Requests an access token for Client Credentials Grant as specified in section 4.4.2
     *
     * @param string $code The authorization code received from the authorization server.
     * @param string $redirectUri REQUIRED, if the "redirect_uri" parameter was included in the authorization request as described in Section 4.1.1, and their values MUST be identical.
     * @param string $clientIdentifier REQUIRED, if the client is not authenticating with the authorization server as described in Section 3.2.1.
     * @return mixed
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.3
     */
    public function requestAuthorizationCodeGrantAccessToken($code, $redirectUri = null, $clientIdentifier = null);

    /**
     * Requests an access token for Resource Owner Password Credentials Grant as specified in section 4.3.2
     *
     * @param string $username The resource owner username.
     * @param string $password The resource owner password.
     * @param array $scope The scope of the access request as described by http://tools.ietf.org/html/rfc6749#section-3.3
     * @return mixed
     * @see http://tools.ietf.org/html/rfc6749#section-4.3.2
     */
    public function requestResourceOwnerPasswordCredentialsGrantAccessToken($username, $password, $scope = array());

    /**
     * Requests an access token for Client Credentials Grant as specified in section 4.4.2
     *
     * @param array $scope The scope of the access request as described by http://tools.ietf.org/html/rfc6749#section-3.3
     * @return mixed
     * @see http://tools.ietf.org/html/rfc6749#section-4.4.2
     */
    public function requestClientCredentialsGrantAccessToken($scope = array());

    /**
     * @return string
     */
    public function __toString();
}
