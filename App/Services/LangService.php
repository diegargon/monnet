<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Lang
{
    /** @var array<string> $language */
    private static array $language = [];

    /** @var string */
    private static string $defaultLang = 'es';

    public function __construct(?string $langCode)
    {
        if (!$langCode) {
            $langCode = self::$defaultLang;
        }
        self::loadLanguage($langCode);
    }

    /**
     *
     * @param string $langCode
     * @return bool
     * @throws Exception
     */
    private static function loadLanguage(string $langCode): bool
    {
        /*
         * We load es as default language to avoid missing keys
         * Then if != we load the selected config language
         *
         */

        /**
         * $lng (in included file)
         *
         * @var array<string,string> $lng
         */

        $langFile = 'lang/' . self::$defaultLang . '/main.lang.php';

        if (file_exists($langFile)) {
            include $langFile;
            self::$language = $lng;
        } else {
            throw new Exception("Lang file '$langFile' not found");
        }

        if ($langCode !== self::$defaultLang) {
            $sel_langfile = 'lang/' . $langCode . '/main.lang.php';
            if (file_exists($sel_langfile)) {
                include $sel_langfile;
                $sel_lang = $lng;
                self::$language = array_merge(self::$language, $sel_lang);
            }
        }

        return true;
    }

    /**
     *
     * @param string $key
     * @return string|false
     */
    public static function get(string $key): string|false
    {
        if (self::$language && isset(self::$language[$key])) :
            return self::$language[$key];
        endif;

        return false;
    }
}
