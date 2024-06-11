<?php

declare(strict_types=1);

namespace App\Api\Wrapper;

use App\Api\Client;
use CrowdinApiClient\Model\Language;

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
