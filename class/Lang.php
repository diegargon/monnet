<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Lang
{
    /** @var array<string> $language */
    private static array $language = [];
    private static string $defaultLang = 'es';
    private static string $selectedLangCode;

    public function __construct()
    {
        self::loadLanguage();
    }

    private static function loadLanguage(?string $langCode = null): bool
    {
        /*
         * We load es as default language to avoid missing keys
         * Then if != we load the selected config language
         *
         */
        //Default lang
        $langFile = 'lang/lang.' . self::$defaultLang . '.php';
        if (file_exists($langFile)) {
            self::$language = include $langFile;
        } else {
            throw new Exception("Lang file '$langFile' not found");
        }
        if (!$langCode) {
            return true;
        }
        //Config lang

        /**
         *  Access to undefined constant Lng::defaultLang. ???
         * @phpstan-ignore-next-line
         */
        if ($langCode !== self::defaultLang) {
            $sel_langfile = 'lang/lang.' . $langCode . '.php';
            if (file_exists($sel_langfile)) {
                self::$selectedLangCode = $langCode;
                $sel_lang = include $sel_langfile;
                self::$language = array_merge(self::$language, $sel_lang);
            }
        }

        return true;
    }

    public static function loadUserLang(string $langCode): bool
    {
        /**
         * Access to an undefined static property Lng::$selLanCode. ????
         * @phpstan-ignore-next-line
         */
        if ($langCode === self::$defaultLang || $langCode === self::$selLanCode) {
            return false;
        }
        $userlangfile = 'lang/lang.' . $langCode . '.php';
        if (file_exists($userlangfile)) {
            $user_lang = include $userlangfile;
            self::$language = array_merge(self::$language, $user_lang);
        }
        return true;
    }

    public static function get(string $key): string|false
    {
        if (self::$language && isset(self::$language[$key])) {
            return self::$language[$key];
        }
        return false;
    }
}
