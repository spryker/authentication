<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Authentication;

use Spryker\Glue\Kernel\AbstractBundleConfig;

class AuthenticationConfig extends AbstractBundleConfig
{
    protected const GRANT_TYPE_PASSWORD = 'password';

    protected const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    /**
     * @api
     */
    public function getPasswordGrantType(): string
    {
        return static::GRANT_TYPE_PASSWORD;
    }

    /**
     * @api
     */
    public function getRefreshTokenGrantType(): string
    {
        return static::GRANT_TYPE_REFRESH_TOKEN;
    }
}
