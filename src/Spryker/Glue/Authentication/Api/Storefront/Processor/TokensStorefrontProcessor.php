<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Authentication\Api\Storefront\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Generated\Api\Storefront\TokensStorefrontResource;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\Client\Oauth\OauthClientInterface;
use Spryker\Glue\Authentication\AuthenticationConfig;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @implements \ApiPlatform\State\ProcessorInterface<\Generated\Api\Storefront\TokensStorefrontResource, \Generated\Api\Storefront\TokensStorefrontResource>
 */
class TokensStorefrontProcessor implements ProcessorInterface
{
    public function __construct(
        protected OauthClientInterface $oauthClient,
        protected AuthenticationConfig $authenticationConfig
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        if ($operation instanceof Post) {
            return $this->processPasswordGrant($data);
        }

        return $data;
    }

    protected function processPasswordGrant(TokensStorefrontResource $resource): TokensStorefrontResource
    {
        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->setGrantType($this->authenticationConfig->getPasswordGrantType())
            ->setUsername($resource->username)
            ->setPassword($resource->password);

        $oauthResponseTransfer = $this->oauthClient->processAccessTokenRequest($oauthRequestTransfer);

        if (!$oauthResponseTransfer->getIsValid()) {
            $errorMessage = $oauthResponseTransfer->getError()?->getMessage() ?? 'Authentication failed';

            throw new HttpException(500, $errorMessage);
        }

        $resource->tokenType = $oauthResponseTransfer->getTokenType();
        $resource->expiresIn = $oauthResponseTransfer->getExpiresIn();
        $resource->accessToken = $oauthResponseTransfer->getAccessToken();
        $resource->refreshToken = $oauthResponseTransfer->getRefreshToken();

        return $resource;
    }
}
