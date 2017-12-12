<?php
namespace Flowpack\OAuth2\Client\Flow;

/*
 * This file is part of the Flowpack.OAuth2.Client package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\OAuth2\Client\Token\AbstractClientToken;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Authentication\TokenInterface;

/**
 */
interface FlowInterface
{
    /**
     * @return AbstractClientToken
     */
    public function getChargedAuthenticatedTokenHavingNoPartyAttached();

    /**
     * @param \Neos\Flow\Security\Authentication\TokenInterface|\Flowpack\OAuth2\Client\Token\AbstractClientToken $token
     * @return TokenInterface
     */
    public function getTokenOfForeignAccountOf(AbstractClientToken $token);

    /**
     * @param AbstractClientToken $token
     * @return Account
     */
    public function getForeignAccountFor(AbstractClientToken $token);

    /**
     * @param AbstractClientToken $token
     */
    public function createPartyAndAttachToAccountFor(AbstractClientToken $token);

    /**
     * @param TokenInterface $foreignAccountToken
     * @param AbstractClientToken $possibleOAuthTokenAuthenticatedWithoutParty
     */
    public function setPartyOfAuthenticatedTokenAndAttachToAccountFor(TokenInterface $foreignAccountToken, AbstractClientToken $possibleOAuthTokenAuthenticatedWithoutParty);

    /**
     * Returns the token class name this flow is responsible for
     *
     * @return string
     */
    public function getTokenClassName();
}
