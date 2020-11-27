<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Api\Wrapper;

use CrowdinApiClient\Model\Language;
use TYPO3\CrowdinBridge\Api\Client;

class LanguageApi extends Client
{

    /**
     * @return Language[]
     */
    public function get(): array
    {
        $out = [];
        $languages = $this->client->language->list(['limit' => 500]);
        foreach ($languages as $language) {
            $out[$language->getId()] = $language;
        }

        return $out;
    }

}
