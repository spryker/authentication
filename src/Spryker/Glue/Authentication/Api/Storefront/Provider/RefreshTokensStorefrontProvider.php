<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Authentication\Api\Storefront\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Generated\Api\Storefront\RefreshTokensStorefrontResource;

/**
 * @implements \ApiPlatform\State\ProviderInterface<\Generated\Api\Storefront\RefreshTokensStorefrontResource>
 */
class RefreshTokensStorefrontProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $refreshToken = $uriVariables['refreshToken'] ?? null;

        if ($refreshToken === null) {
            return null;
        }

        $resource = new RefreshTokensStorefrontResource();
        $resource->setRefreshToken($refreshToken);

        return $resource;
    }
}
