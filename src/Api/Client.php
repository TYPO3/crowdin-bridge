<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Api;


use CrowdinApiClient\Crowdin;
use TYPO3\CrowdinBridge\Entity\BridgeConfiguration;

class Client
{

    /** @var Crowdin */
    protected Crowdin $client;

    /** @var BridgeConfiguration */
    protected BridgeConfiguration $configuration;

    public function __construct()
    {
        $this->configuration = new BridgeConfiguration();

        $accessToken = (string)getenv('CROWDIN_ACCESS_TOKEN');
        if (!$accessToken) {
            throw new \UnexpectedValueException('env CROWDIN_ACCESS_TOKEN missing');
        }
        $crowdinConfiguration = [
            'access_token' => $accessToken,
//            'organization' => '<organization_domain>', // optional
        ];

        $this->client = new Crowdin($crowdinConfiguration);
    }

    /**
     * @return BridgeConfiguration
     */
    public function getConfiguration(): BridgeConfiguration
    {
        return $this->configuration;
    }

}
