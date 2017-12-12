<?php
namespace Flowpack\OAuth2\Client;

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
use Neos\Flow\Http\Uri;

/**
 * @Flow\Scope("singleton")
 */
class UriBuilder
{
    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $providerOptionsByProviderName = array();

    /**
     * @param string $providerName The name of the authentication provider as used in the Settings
     * @throws \InvalidArgumentException
     * @return Uri
     */
    public function getAuthorizationUri($providerName)
    {
        $providersOptions = $this->getConfiguredOptionsByProviderName($providerName);
        $uri = new Uri($providersOptions['authorizationEndpointUri']);
        $presentQuery = (string)$uri->getQuery();
        $presentQuery = ($presentQuery ? $presentQuery . '&' : '') . http_build_query(array(
            'client_id' => $providersOptions['clientIdentifier'],
            'response_type' => $providersOptions['responseType'],
            'scope' => implode(' ', $providersOptions['scopes']),
            'display' => $providersOptions['display'],
            'redirect_uri' => $this->getRedirectionEndpointUri($providerName)
        ));
        $uri->setQuery($presentQuery);

        return $uri;
    }

    /**
     * @param string $providerName The name of the authentication provider as used in the Settings
     * @return string
     */
    public function getRedirectionEndpointUri($providerName)
    {
        $providersOptions = $this->getConfiguredOptionsByProviderName($providerName);
        return $providersOptions['redirectionEndpointUri'] . '?__oauth2Provider=' . $providerName;
    }

    /**
     * @param string $providerName
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function getConfiguredOptionsByProviderName($providerName)
    {
        if (!array_key_exists($providerName, $this->providerOptionsByProviderName)) {
            $providerOptions = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, sprintf('Neos.Flow.security.authentication.providers.%s.providerOptions', $providerName));
            if (!is_array($providerOptions)) {
                throw new \InvalidArgumentException(sprintf('The given provider name "%s" was not properly defined in the Settings (i.e. being defined and having a "providerOptions" key).', $providerName), 1383739910);
            }
            $this->providerOptionsByProviderName[$providerName] = $providerOptions;
        }
        return $this->providerOptionsByProviderName[$providerName];
    }
}
