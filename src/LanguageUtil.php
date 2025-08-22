<?php

namespace struktal\Translator;

class LanguageUtil {
    /**
     * Get a sorted list of preferred locales from the Accept-Language header
     * @return array
     */
    public static function getPreferredLocalesFromHeader(): array {
        $header = $_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? "";
        $header = trim($header);

        // Match regex against header to extract the language parts
        $regex = "/[a-zA-Z]{1,8}(?:-[a-zA-Z]{1,8}){0,2}(?:;q=[0-9]+(?:\.[0-9]+)?)?/";
        preg_match_all($regex, $header, $headerParts);
        $headerParts = $headerParts[0];

        // Interpret the language parts
        $preferredLocales = array_map(function(string $part) {
            if(!$part) {
                return null;
            }

            $bits = explode(";", $part);
            $localeTag = explode("-", $bits[0]);
            $hasRegion = count($localeTag) >= 2;
            $hasScript = count($localeTag) === 3;

            return [
                "code" => $bits[0],
                "priority" => count($bits) > 1 ? floatval(explode("=", $bits[1])[1]) : 1.0,
                "language" => $localeTag[0],
                "script" => $hasScript ? $localeTag[1] : null,
                "region" => $hasRegion ? $localeTag[$hasScript ? 2 : 1] : null
            ];
        }, $headerParts);

        // Filter out null values
        $preferredLocales = array_filter($preferredLocales, function($part) {
            return $part !== null;
        });

        // Sort locales by their priority
        usort($preferredLocales, function($a, $b) {
            return $b["priority"] <=> $a["priority"];
        });

        return $preferredLocales;
    }

    /**
     * Returns the user's preferred locale
     * @param string $fallback The fallback locale, if the preferred locale is not available
     * @return string
     */
    public static function getPreferredLocale(string $fallback = "en_US"): string {
        $preferredLocales = self::getPreferredLocalesFromHeader();
        $existingLanguages = Translator::getAvailableLocales();

        foreach($preferredLocales as $preferredLocale) {
            $language = $preferredLocale["language"];
            $script = $preferredLocale["script"];
            $region = $preferredLocale["region"];
            $regex = $language;
            if($script) {
                $regex .= "_" . $script;
            } else {
                $regex .= "(_[a-zA-Z]+)?";
            }
            if($region) {
                $regex .= "_" . $region;
            } else {
                $regex .= "(_[a-zA-Z]{2})?";
            }

            foreach($existingLanguages as $existingLanguage) {
                if(preg_match("/^$regex$/", $existingLanguage)) {
                    return $existingLanguage;
                }
            }
        }

        // Fallback
        return $fallback;
    }
}
