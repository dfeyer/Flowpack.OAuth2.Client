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
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

/**
 * @Flow\Scope("singleton")
 */
class Resolver
{
    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param string $providerName The provider name as given in Settings.yaml
     * @throws \InvalidArgumentException
     * @return TokenEndpointInterface
     */
    public function getTokenEndpointForProvider($providerName)
    {
        $tokenEndpointClassName = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, sprintf('Neos.Flow.security.authentication.providers.%s.providerOptions.tokenEndpointClassName', $providerName));
        if ($tokenEndpointClassName === null) {
            throw new \InvalidArgumentException(sprintf('In Settings.yaml, there was no "tokenEndpointClassName" option given for the provider "%s".', $providerName), 1383743372);
        }
        return $this->objectManager->get($tokenEndpointClassName);
    }
}
