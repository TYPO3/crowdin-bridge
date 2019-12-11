<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\ExtendedApi;

use Akeneo\Crowdin\Api\AbstractApi;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Basic information about core
 */
class AccountGetProjects extends AbstractApi
{
    /** @var string */
    protected $accountKey = '';

    /** @var string */
    protected $username = '';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->addUrlParameter('account-key', $this->accountKey);
        $this->addUrlParameter('login', $this->username);
        $this->addUrlParameter('json', 1);

        $path = sprintf(
            "account/get-projects?%s",
            $this->getUrlQueryString()
        );

        $response = $this->client->getHttpClient()->get($path);
        return $response->getBody();
    }

    /**
     * @param string $accountKey
     * @return AccountGetProjects
     */
    public function setAccountKey(string $accountKey): AccountGetProjects
    {
        $this->accountKey = $accountKey;
        return $this;
    }

    /**
     * @param string $username
     * @return AccountGetProjects
     */
    public function setUsername(string $username): AccountGetProjects
    {
        $this->username = $username;
        return $this;
    }
}
