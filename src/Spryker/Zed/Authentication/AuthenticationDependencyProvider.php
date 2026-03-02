<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Authentication;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\Authentication\AuthenticationConfig getConfig()
 */
class AuthenticationDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const PLUGINS_AUTHENTICATION_SERVER = 'PLUGINS_AUTHENTICATION_SERVER';

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addAuthenticationServerPlugins($container);

        return $container;
    }

    protected function addAuthenticationServerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_AUTHENTICATION_SERVER, function (Container $container) {
            return $this->getAuthenticationServerPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Shared\AuthenticationExtension\Dependency\Plugin\AuthenticationServerPluginInterface>
     */
    protected function getAuthenticationServerPlugins(): array
    {
        return [];
    }
}
