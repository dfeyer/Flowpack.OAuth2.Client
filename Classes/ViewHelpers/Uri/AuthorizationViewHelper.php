<?php
namespace Flowpack\OAuth2\Client\ViewHelpers\Uri;

/*
 * This file is part of the Flowpack.OAuth2.Client package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\OAuth2\Client\UriBuilder;
use Neos\Flow\Annotations as Flow;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;

/**
 */
class AuthorizationViewHelper extends AbstractViewHelper
{
    /**
     * @Flow\Inject
     * @var UriBuilder
     */
    protected $oauthUriBuilder;

    /**
     * @param string $providerName The name of the authentication provider as defined in the Settings
     * @return string
     */
    public function render($providerName)
    {
        return $this->oauthUriBuilder->getAuthorizationUri($providerName);
    }
}
