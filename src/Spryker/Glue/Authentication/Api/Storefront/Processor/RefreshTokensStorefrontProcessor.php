<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Authentication\Api\Storefront\Processor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Generated\Api\Storefront\RefreshTokensStorefrontResource;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\Client\Oauth\OauthClientInterface;
use Spryker\Glue\Authentication\AuthenticationConfig;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @implements \ApiPlatform\State\ProcessorInterface<\Generated\Api\Storefront\RefreshTokensStorefrontResource, \Generated\Api\Storefront\RefreshTokensStorefrontResource|null>
 */
class RefreshTokensStorefrontProcessor implements ProcessorInterface
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
            return $this->processRefreshTokenGrant($data);
        }

        if ($operation instanceof Delete) {
            $this->processRefreshTokenRevocation($uriVariables['refreshToken'] ?? '');

            return null;
        }

        return null;
    }

    protected function processRefreshTokenGrant(RefreshTokensStorefrontResource $resource): RefreshTokensStorefrontResource
    {
        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->setGrantType($this->authenticationConfig->getRefreshTokenGrantType())
            ->setRefreshToken($resource->getRefreshToken());

        $oauthResponseTransfer = $this->oauthClient->processAccessTokenRequest($oauthRequestTransfer);

        if (!$oauthResponseTransfer->getIsValid()) {
            $errorMessage = $oauthResponseTransfer->getError()?->getMessage() ?? 'Token refresh failed';

            throw new HttpException(500, $errorMessage);
        }

        $resource->tokenType = $oauthResponseTransfer->getTokenType();
        $resource->expiresIn = $oauthResponseTransfer->getExpiresIn();
        $resource->accessToken = $oauthResponseTransfer->getAccessToken();
        $resource->refreshToken = $oauthResponseTransfer->getRefreshToken();

        return $resource;
    }

    protected function processRefreshTokenRevocation(string $refreshToken): void
    {
        $customerReference = $this->extractCustomerReferenceFromToken($refreshToken);

        $revokeResponseTransfer = $this->oauthClient->revokeRefreshToken($refreshToken, $customerReference);

        if (!$revokeResponseTransfer->getIsSuccessful()) {
            throw new HttpException(500, 'Token revocation failed');
        }
    }

    protected function extractCustomerReferenceFromToken(string $refreshToken): string
    {
        // TODO: Extract customer reference from JWT refresh token
        return '';
    }
}
