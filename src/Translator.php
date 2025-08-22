<?php

namespace struktal\Translator;

class Translator {
    private static string $translationsDirectory = "";
    private static string $domain = "messages";
    private static string $locale = "en_US";
    /** @var resource|null $translationFile */
    private static $translationFile = null;

    public static function setTranslationsDirectory(string $path): void {
        self::$translationsDirectory = rtrim($path, "/");
    }

    public static function setDomain(string $domain): void {
        self::$domain = $domain;
        self::openTranslationFile();
    }

    public static function setLocale(string $locale): void {
        self::$locale = $locale;
        self::openTranslationFile();
    }

    private static function openTranslationFile(): void {
        if(self::$translationFile && is_resource(self::$translationFile)) {
            fclose(self::$translationFile);
        }

        try {
            self::$translationFile = fopen(self::$translationsDirectory . "/" . self::$locale . "/" . self::$domain . ".json", "r");
        } catch(\Exception $e) {
            self::$translationFile = null;
            throw new \RuntimeException("Could not open translation file: " . self::$translationsDirectory . "/" . self::$locale . "/" . self::$domain . ".json", 0, $e);
        }
    }

    public static function translate(string $message, array $variables = []): string {
        if(!apcu_exists(self::$locale . "-" . self::$domain)) {
            // Read translations from file
            if(self::$translationFile && is_resource(self::$translationFile)) {
                $translations = fread(self::$translationFile, filesize(self::$translationsDirectory . "/" . self::$locale . "/" . self::$domain . ".json"));
                $translations = json_decode($translations, true);

                // Store translations in cache
                apcu_store(self::$locale . "-" . self::$domain, $translations);

                if(isset($translations[$message])) {
                    $message = $translations[$message];
                } else {
                    trigger_error("Translation not found: \"{$message}\"", E_USER_WARNING);
                }

                fseek(self::$translationFile, 0);
            }
        } else {
            // Read translations from cache
            $translations = apcu_fetch(self::$locale . "-" . self::$domain);
            if(isset($translations[$message])) {
                $message = $translations[$message];
            } else {
                trigger_error("Translation not found: \"{$message}\"", E_USER_WARNING);
            }
        }

        // Replace the variables in the message
        $messageParts = explode("$$", $message);
        if(count($messageParts) > 1) {
            $message = $messageParts[0];
            for($i = 1; $i < count($messageParts); $i++) {
                $messagePart = $messageParts[$i];
                if($i % 2 === 0) {
                    $message .= $messagePart;
                    continue;
                }

                if(array_key_exists($messagePart, $variables)) {
                    $message .= $variables[$messagePart];
                } else {
                    $message .= "$$" . $messagePart;
                    if($i < count($messageParts) - 1) {
                        $message .= "$$";
                    }
                }
            }
        }

        return $message;
    }

    public static function getLocaleForHtmlLang(): string {
        return str_replace("_", "-", self::$locale);
    }

    public static function getAvailableLocales(): array {
        $directories = scandir(self::$translationsDirectory);
        return array_filter($directories, function($directory) {
            return is_dir(self::$translationsDirectory . "/" . $directory) && $directory !== "." && $directory !== "..";
        });
    }
}
