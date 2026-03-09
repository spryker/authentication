<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Glue\Authentication\StorefrontApi;

use Codeception\Stub;
use Generated\Shared\Transfer\OauthErrorTransfer;
use Generated\Shared\Transfer\OauthResponseTransfer;
use Generated\Shared\Transfer\RevokeRefreshTokenResponseTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Client\Oauth\OauthClientInterface;
use Spryker\Client\Store\StoreClientInterface;
use SprykerTest\ApiPlatform\Test\StorefrontApiTestCase;
use SprykerTest\Glue\Authentication\StorefrontApiTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group Authentication
 * @group StorefrontApi
 * @group AuthenticationStorefrontApiTest
 * Add your own group annotations below this line
 */
class AuthenticationStorefrontApiTest extends StorefrontApiTestCase
{
    protected StorefrontApiTester $tester;

    public function testGivenValidCredentialsWhenCreatingTokenViaPostThenTokenIsReturnedSuccessfully(): void
    {
        // Arrange
        $requestData = [
            'data' => [
                'type' => 'tokens',
                'attributes' => ['username' => 'john.doe@example.com', 'password' => 'SecurePassword123!'],
            ],
        ];

        $oauthResponseTransfer = (new OauthResponseTransfer())
            ->setIsValid(true)
            ->setTokenType('Bearer')
            ->setExpiresIn(3600)
            ->setAccessToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...')
            ->setRefreshToken('def50200...');

        $clientStub = Stub::makeEmpty(OauthClientInterface::class, [
            'processAccessTokenRequest' => $oauthResponseTransfer,
        ]);

        $this->getContainer()->set(OauthClientInterface::class, $clientStub);
        $this->mockStoreClient();

        // Act
        $this->createClient()->request('POST', '/tokens', ['json' => $requestData]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'data' => [
                'type' => 'tokens',
                'attributes' => [
                    'tokenType' => 'Bearer',
                    'expiresIn' => 3600,
                ],
            ],
        ]);
    }

    public function testGivenInvalidCredentialsWhenCreatingTokenViaPostThenErrorIsReturned(): void
    {
        // Arrange
        $requestData = [
            'data' => [
                'type' => 'tokens',
                'attributes' => ['username' => 'john.doe@example.com', 'password' => 'wrong-password'],
            ],
        ];

        $oauthErrorTransfer = (new OauthErrorTransfer())
            ->setMessage('Invalid credentials');

        $oauthResponseTransfer = (new OauthResponseTransfer())
            ->setIsValid(false)
            ->setError($oauthErrorTransfer);

        $clientStub = Stub::makeEmpty(OauthClientInterface::class, [
            'processAccessTokenRequest' => $oauthResponseTransfer,
        ]);

        $this->getContainer()->set(OauthClientInterface::class, $clientStub);
        $this->mockStoreClient();

        $glossaryStub = Stub::makeEmpty(GlossaryStorageClientInterface::class, [
            'translate' => function (string $id): string {
                return sprintf('%s-translated-by-mock', $id);
            },
        ]);
        $this->getContainer()->set(GlossaryStorageClientInterface::class, $glossaryStub);

        // Act
        $this->createClient()->request('POST', '/tokens', ['json' => $requestData]);

        // Assert
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['@type' => 'Error']);
    }

    public function testGivenValidRefreshTokenWhenRefreshingTokenViaPostThenNewTokenIsReturned(): void
    {
        // Arrange
        $requestData = [
            'data' => [
                'type' => 'refresh-tokens',
                'attributes' => ['refreshToken' => 'def50200validrefreshtoken'],
            ],
        ];

        $oauthResponseTransfer = (new OauthResponseTransfer())
            ->setIsValid(true)
            ->setTokenType('Bearer')
            ->setExpiresIn(3600)
            ->setAccessToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...NEW')
            ->setRefreshToken('def50200newrefreshtoken');

        $clientStub = Stub::makeEmpty(OauthClientInterface::class, [
            'processAccessTokenRequest' => $oauthResponseTransfer,
        ]);

        $this->getContainer()->set(OauthClientInterface::class, $clientStub);
        $this->mockStoreClient();

        // Act
        $this->createClient()->request('POST', '/refresh-tokens', ['json' => $requestData]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'data' => [
                'type' => 'refresh-tokens',
                'attributes' => [
                    'tokenType' => 'Bearer',
                    'expiresIn' => 3600,
                ],
            ],
        ]);
    }

    public function testGivenInvalidRefreshTokenWhenRefreshingTokenViaPostThenErrorIsReturned(): void
    {
        // Arrange
        $requestData = [
            'data' => [
                'type' => 'refresh-tokens',
                'attributes' => ['refreshToken' => 'invalid-token'],
            ],
        ];

        $oauthErrorTransfer = (new OauthErrorTransfer())
            ->setMessage('Invalid refresh token');

        $oauthResponseTransfer = (new OauthResponseTransfer())
            ->setIsValid(false)
            ->setError($oauthErrorTransfer);

        $clientStub = Stub::makeEmpty(OauthClientInterface::class, [
            'processAccessTokenRequest' => $oauthResponseTransfer,
        ]);

        $this->getContainer()->set(OauthClientInterface::class, $clientStub);

        $glossaryStub = Stub::makeEmpty(GlossaryStorageClientInterface::class, [
            'translate' => function (string $id): string {
                return sprintf('%s-translated-by-mock', $id);
            },
        ]);
        $this->getContainer()->set(GlossaryStorageClientInterface::class, $glossaryStub);
        $this->mockStoreClient();

        // Act
        $this->createClient()->request('POST', '/refresh-tokens', ['json' => $requestData]);

        // Assert
        $this->assertResponseStatusCodeSame(500);
        $this->assertJsonContains(['@type' => 'Error']);
    }

    public function testGivenValidRefreshTokenWhenRevokingViaDeleteThenTokenIsRevoked(): void
    {
        // Arrange
        $refreshToken = 'def50200validrefreshtoken';

        $revokeResponseTransfer = (new RevokeRefreshTokenResponseTransfer())
            ->setIsSuccessful(true);

        $clientStub = Stub::makeEmpty(OauthClientInterface::class, [
            'revokeRefreshToken' => $revokeResponseTransfer,
        ]);

        $this->getContainer()->set(OauthClientInterface::class, $clientStub);
        $this->mockStoreClient();

        // Act
        $this->createClient()->request('DELETE', '/refresh-tokens/' . $refreshToken);

        // Assert
        $this->assertResponseStatusCodeSame(204);
    }

    protected function mockStoreClient(): void
    {
        $storeTransfer = new StoreTransfer();
        $storeTransfer->setAvailableLocaleIsoCodes(['de' => 'de_DE', 'en' => 'en_US']);

        $clientStub = Stub::makeEmpty(StoreClientInterface::class, [
            'getCurrentStore' => $storeTransfer,
        ]);

        $this->getContainer()->set(StoreClientInterface::class, $clientStub);
    }
}
