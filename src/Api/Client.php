<?php

declare(strict_types=1);

namespace App\Api;

use App\Entity\BridgeConfiguration;
use CrowdinApiClient\Crowdin;

class Client
{
    protected Crowdin $client;
    protected BridgeConfiguration $configuration;

    public function __construct()
    {
        $this->configuration = new BridgeConfiguration();

        $accessToken = $_ENV['CROWDIN_ACCESS_TOKEN'] ?? (string)getenv('CROWDIN_ACCESS_TOKEN');
        if (!$accessToken) {
            throw new \UnexpectedValueException('env CROWDIN_ACCESS_TOKEN missing');
        }
        $crowdinConfiguration = [
            'access_token' => $accessToken,
            //            'organization' => '<organization_domain>', // optional
        ];

        $this->client = new Crowdin($crowdinConfiguration);
    }

    public function getConfiguration(): BridgeConfiguration
    {
        return $this->configuration;
    }

}
