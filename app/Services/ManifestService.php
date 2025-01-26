<?php

namespace App\Services;

class ManifestService
{
    public static function loadManifestData(string $apiFileLink): array
    {
        return json_decode(file_get_contents($apiFileLink), true);
    }

    public static function replacePlaceholders(string $search, string $replacer, string $textWithPlaceholders): string
    {
        return str_replace(
            $search,
            $replacer,
            $textWithPlaceholders
        );
    }

    public static function getPageText(string $eg): array
    {
        if ($_SESSION['API_TEXT'] == null) {
            $_SESSION['API_TEXT'] = ManifestService::loadManifestData(
                $GLOBALS['DS_CONFIG']['CodeExamplesManifest']
            );
        }

        $apiType = ManifestService::getAPIByLink(preg_replace('/[0-9]+/', '', $eg));
        $apis = $_SESSION['API_TEXT']['APIs'];
        $result = [];
        $CodeExampleNumber = (int) filter_var($eg, FILTER_SANITIZE_NUMBER_INT);

        foreach ($apis as $api) {
            if ($api["Name"] === $apiType) {
                $groups = $api["Groups"];

                foreach ($groups as $group) {
                    foreach ($group["Examples"] as $example) {
                        if ($example["ExampleNumber"] === $CodeExampleNumber) {
                            $result = $example;
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public static function getCommonTexts(): array
    {
        if ($_SESSION['API_TEXT'] == null) {
            $_SESSION['API_TEXT'] = ManifestService::loadManifestData(
                $GLOBALS['DS_CONFIG']['CodeExamplesManifest']
            );
        }

        $commonText = $_SESSION['API_TEXT']['SupportingTexts'];

        return $commonText;
    }

    public static function getAPIByLink(string $link): string
    {
        $link = preg_replace('/\d/', '', $link);

        $currentAPI = 'signature';

        return $currentAPI;
    }
}
