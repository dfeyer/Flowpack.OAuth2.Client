<?php

namespace Flowpack\OAuth2\Client\Token;

/*
 * This file is part of the Flowpack.OAuth2.Client package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\OAuth2\Client\Endpoint\Resolver;
use Flowpack\OAuth2\Client\Endpoint\TokenEndpointInterface;
use Flowpack\OAuth2\Client\Exception;
use Flowpack\OAuth2\Client\UriBuilder;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\SecurityLoggerInterface;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Exception\NoSuchArgumentException;
use Neos\Flow\Security\Authentication\Token\AbstractToken;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Exception\InvalidAuthenticationStatusException;

abstract class AbstractClientToken extends AbstractToken
{
    /**
     * @Flow\Inject
     * @var Resolver
     */
    protected $endpointResolver;

    /**
     * @Flow\Inject
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @Flow\Inject
     * @var SecurityLoggerInterface
     */
    protected $securityLogger;

    /**
     * @var TokenEndpointInterface
     */
    protected $tokenEndpoint;

    /**
     * @var array
     */
    protected $credentials = ['accessToken' => null];

    /**
     * The $this->authenticationProviderName property is either known when in session
     * or is set manually via the setAuthenticationProviderName. That's why we can't rely
     * on this value being present already.
     */
    protected function initializeObject()
    {
        if ($this->authenticationProviderName !== null) {
            $this->tokenEndpoint = $this->endpointResolver->getTokenEndpointForProvider($this->authenticationProviderName);
        }
    }

    /**
     * Updates the authentication credentials, the authentication manager needs to authenticate this token.
     * This could be a username/password from a login controller.
     * This method is called while initializing the security context. By returning TRUE you
     * make sure that the authentication manager will (re-)authenticate the tokens with the current credentials.
     * Note: You should not persist the credentials!
     *
     * @param ActionRequest $actionRequest The current request instance
     * @throws \InvalidArgumentException
     * @return boolean TRUE if this token needs to be (re-)authenticated
     * @throws InvalidAuthenticationStatusException
     */
    public function updateCredentials(ActionRequest $actionRequest)
    {
        if ($actionRequest->getHttpRequest()->getMethod() !== 'GET'
            || $actionRequest->getInternalArgument('__oauth2Provider') !== $this->authenticationProviderName) {
            return;
        }

        try {
            $code = $actionRequest->getArgument('code');
        } catch (NoSuchArgumentException $exception) {
            $this->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            $this->securityLogger->log('There was no argument `code` provided.', LOG_NOTICE);
            return;
        }
        $redirectUri = $this->uriBuilder->getRedirectionEndpointUri($this->authenticationProviderName);
        try {
            //            $this->credentials['accessToken'] = $this->tokenEndpoint->requestAuthorizationCodeGrantAccessToken($code, $redirectUri);
            $this->credentials = $this->tokenEndpoint->requestAuthorizationCodeGrantAccessToken($code, $redirectUri);
            $this->setAuthenticationStatus(TokenInterface::AUTHENTICATION_NEEDED);
        } catch (Exception $exception) {
            $this->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            $this->securityLogger->logException($exception);
            return;
        }
    }

    /**
     * @throws Exception
     * @return string
     */
    public function __toString()
    {
        if ($this->tokenEndpoint === null) {
            throw new Exception('The token endpoint implementation is not yet known to the token', 1384172817);
        }
        return (string)$this->tokenEndpoint;
    }

    /**
     * @param string $authenticationProviderName
     */
    public function setAuthenticationProviderName($authenticationProviderName)
    {
        parent::setAuthenticationProviderName($authenticationProviderName);
        $this->tokenEndpoint = $this->endpointResolver->getTokenEndpointForProvider($this->authenticationProviderName);
    }
}
