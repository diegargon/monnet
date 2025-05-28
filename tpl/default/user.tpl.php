<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

$timezones = [
    "Pacific/Midway" => "(UTC-11:00) Midway Island, American Samoa",
    "Pacific/Honolulu" => "(UTC-10:00) Hawaii",
    "America/Anchorage" => "(UTC-08:00) Alaska",
    "America/Los_Angeles" => "(UTC-07:00) Pacific Time (US and Canada)",
    "America/Tijuana" => "(UTC-07:00) Baja California",
    "America/Phoenix" => "(UTC-07:00) Arizona",
    "America/Denver" => "(UTC-06:00) Mountain Time (US and Canada)",
    "America/Chihuahua" => "(UTC-06:00) Chihuahua, La Paz, Mazatlan",
    "America/Belize" => "(UTC-06:00) Central America",
    "America/Regina" => "(UTC-06:00) Saskatchewan",
    "America/Chicago" => "(UTC-05:00) Central Time (US and Canada)",
    "America/Mexico_City" => "(UTC-05:00) Guadalajara, Mexico City, Monterrey",
    "America/Bogota" => "(UTC-05:00) Bogota, Lima, Quito",
    "America/Jamaica" => "(UTC-05:00) Kingston, George Town",
    "America/Manaus" => "(UTC-04:00) Georgetown, La Paz, Manaus, San Juan",
    "America/Cuiaba" => "(UTC-04:00) Cuiaba",
    "America/New_York" => "(UTC-04:00) Eastern Time (US and Canada)",
    "America/Indiana/Indianapolis" => "(UTC-04:00) Indiana (East)",
    "America/Caracas" => "(UTC-04:30) Caracas",
    "America/Halifax" => "(UTC-03:00) Atlantic Time (Canada)",
    "America/Asuncion" => "(UTC-03:00) Asuncion",
    "America/Sao_Paulo" => "(UTC-03:00) Brasilia",
    "America/Buenos_Aires" => "(UTC-03:00) Buenos Aires",
    "America/Cayenne" => "(UTC-03:00) Cayenne, Fortaleza",
    "America/Montevideo" => "(UTC-03:00) Montevideo",
    "America/Bahia" => "(UTC-03:00) Salvador",
    "America/Santiago" => "(UTC-03:00) Santiago",
    "America/St_Johns" => "(UTC-02:30) Newfoundland and Labrador",
    "America/Godthab" => "(UTC-02:00) Greenland",
    "America/Noronha" => "(UTC-02:00) Mid-Atlantic",
    "Atlantic/Cape_Verde" => "(UTC-01:00) Cape Verde Islands",
    "Africa/Monrovia" => "(UTC+00:00) Monrovia, Reykjavik",
    "Atlantic/Azores" => "(UTC+00:00) Azores",
    "Europe/London" => "(UTC+01:00) Dublin, Edinburgh, Lisbon, London",
    "Africa/Casablanca" => "(UTC+01:00) Casablanca",
    "Africa/Algiers" => "(UTC+01:00) West Central Africa",
    "Europe/Amsterdam" => "(UTC+02:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
    "Europe/Belgrade" => "(UTC+02:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague",
    "Europe/Brussels" => "(UTC+02:00) Brussels, Copenhagen, Madrid, Paris",
    "Europe/Warsaw" => "(UTC+02:00) Sarajevo, Skopje, Warsaw, Zagreb",
    "Africa/Cairo" => "(UTC+02:00) Cairo",
    "Africa/Harare" => "(UTC+02:00) Harare, Pretoria",
    "Europe/Kaliningrad" => "(UTC+02:00) Kaliningrad",
    "Africa/Tripoli" => "(UTC+02:00) Tripoli",
    "Africa/Windhoek" => "(UTC+02:00) Windhoek",
    "Europe/Athens" => "(UTC+03:00) Athens, Bucharest",
    "Asia/Beirut" => "(UTC+03:00) Beirut",
    "Asia/Damascus" => "(UTC+03:00) Damascus",
    "EET" => "(UTC+03:00) Eastern Europe",
    "Europe/Helsinki" => "(UTC+03:00) Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius",
    "Asia/Istanbul" => "(UTC+03:00) Istanbul",
    "Asia/Jerusalem" => "(UTC+03:00) Jerusalem",
    "Asia/Amman" => "(UTC+03:00) Amman",
    "Asia/Baghdad" => "(UTC+03:00) Baghdad",
    "Asia/Kuwait" => "(UTC+03:00) Kuwait, Riyadh",
    "Europe/Minsk" => "(UTC+03:00) Minsk",
    "Europe/Moscow" => "(UTC+03:00) Moscow, St. Petersburg, Volgograd",
    "Africa/Nairobi" => "(UTC+03:00) Nairobi",
    "Asia/Tehran" => "(UTC+03:30) Tehran",
    "Asia/Muscat" => "(UTC+04:00) Abu Dhabi, Muscat",
    "Europe/Samara" => "(UTC+04:00) Izhevsk, Samara",
    "Indian/Mauritius" => "(UTC+04:00) Port Louis",
    "Asia/Tbilisi" => "(UTC+04:00) Tbilisi",
    "Asia/Yerevan" => "(UTC+04:00) Yerevan",
    "Asia/Baku" => "(UTC+05:00) Baku",
    "Asia/Kabul" => "(UTC+04:30) Kabul",
    "Asia/Yekaterinburg" => "(UTC+05:00) Ekaterinburg",
    "Asia/Tashkent" => "(UTC+05:00) Tashkent, Ashgabat",
    "Asia/Karachi" => "(UTC+05:00) Islamabad, Karachi",
    "Asia/Kolkata" => "(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi",
    "Asia/Colombo" => "(UTC+05:30) Sri Jayawardenepura",
    "Asia/Katmandu" => "(UTC+05:45) Kathmandu",
    "Asia/Almaty" => "(UTC+06:00) Astana",
    "Asia/Dhaka" => "(UTC+06:00) Dhaka",
    "Asia/Novosibirsk" => "(UTC+06:00) Novosibirsk",
    "Asia/Rangoon" => "(UTC+06:30) Yangon (Rangoon)",
    "Asia/Bangkok" => "(UTC+07:00) Bangkok, Hanoi, Jakarta",
    "Asia/Krasnoyarsk" => "(UTC+07:00) Krasnoyarsk",
    "Asia/Chongqing" => "(UTC+08:00) Beijing, Chongqing, Hong Kong SAR, Urumqi",
    "Asia/Irkutsk" => "(UTC+08:00) Irkutsk",
    "Asia/Kuala_Lumpur" => "(UTC+08:00) Kuala Lumpur, Singapore",
    "Australia/Perth" => "(UTC+08:00) Perth",
    "Asia/Taipei" => "(UTC+08:00) Taipei",
    "Asia/Ulaanbaatar" => "(UTC+08:00) Ulaanbaatar",
    "Asia/Tokyo" => "(UTC+09:00) Osaka, Sapporo, Tokyo",
    "Asia/Seoul" => "(UTC+09:00) Seoul",
    "Asia/Yakutsk" => "(UTC+09:00) Yakutsk",
    "Australia/Darwin" => "(UTC+09:30) Darwin",
    "Australia/Brisbane" => "(UTC+10:00) Brisbane",
    "Pacific/Guam" => "(UTC+10:00) Guam, Port Moresby",
    "Asia/Magadan" => "(UTC+10:00) Magadan",
    "Asia/Vladivostok" => "(UTC+10:00) Vladivostok, Magadan",
    "Australia/Adelaide" => "(UTC+10:30) Adelaide",
    "Australia/Canberra" => "(UTC+11:00) Canberra, Melbourne, Sydney",
    "Australia/Hobart" => "(UTC+11:00) Hobart",
    "Asia/Srednekolymsk" => "(UTC+11:00) Chokirdakh",
    "Pacific/Guadalcanal" => "(UTC+11:00) Solomon Islands, New Caledonia",
    "Asia/Anadyr" => "(UTC+12:00) Anadyr, Petropavlovsk-Kamchatsky",
    "Pacific/Fiji" => "(UTC+12:00) Fiji Islands, Kamchatka, Marshall Islands",
    "Pacific/Auckland" => "(UTC+13:00) Auckland, Wellington",
    "Pacific/Tongatapu" => "(UTC+13:00) Nuku'alofa",
    "Pacific/Apia" => "(UTC+14:00) Samoa"
];

// Array de idiomas
$langs = [
    "en" => "English",
    "es" => "Spanish",
    "af" => "Afrikaans",
    "sq" => "Albanian",
    "am" => "Amharic",
    "ar" => "Arabic",
    "hy" => "Armenian",
    "az" => "Azerbaijani",
    "eu" => "Basque",
    "be" => "Belarusian",
    "bn" => "Bengali",
    "bs" => "Bosnian",
    "bg" => "Bulgarian",
    "ca" => "Catalan",
    "ceb" => "Cebuano",
    "ny" => "Chichewa",
    "zh-CN" => "Chinese",
    "co" => "Corsican",
    "hr" => "Croatian",
    "cs" => "Czech",
    "da" => "Danish",
    "nl" => "Dutch",
    "eo" => "Esperanto",
    "et" => "Estonian",
    "tl" => "Filipino",
    "fi" => "Finnish",
    "fr" => "French",
    "fy" => "Frisian",
    "gl" => "Galician",
    "ka" => "Georgian",
    "de" => "German",
    "el" => "Greek",
    "gu" => "Gujarati",
    "ht" => "Haitian Creole",
    "ha" => "Hausa",
    "haw" => "Hawaiian",
    "iw" => "Hebrew",
    "hi" => "Hindi",
    "hmn" => "Hmong",
    "hu" => "Hungarian",
    "is" => "Icelandic",
    "ig" => "Igbo",
    "id" => "Indonesian",
    "ga" => "Irish",
    "it" => "Italian",
    "ja" => "Japanese",
    "jw" => "Javanese",
    "kn" => "Kannada",
    "kk" => "Kazakh",
    "km" => "Khmer",
    "ko" => "Korean",
    "ku" => "Kurdish (Kurmanji)",
    "ky" => "Kyrgyz",
    "lo" => "Lao",
    "la" => "Latin",
    "lv" => "Latvian",
    "lt" => "Lithuanian",
    "lb" => "Luxembourgish",
    "mk" => "Macedonian",
    "mg" => "Malagasy",
    "ms" => "Malay",
    "ml" => "Malayalam",
    "mt" => "Maltese",
    "mi" => "Maori",
    "mr" => "Marathi",
    "mn" => "Mongolian",
    "my" => "Myanmar (Burmese)",
    "ne" => "Nepali",
    "no" => "Norwegian",
    "ps" => "Pashto",
    "fa" => "Persian",
    "pl" => "Polish",
    "pt" => "Portuguese",
    "pa" => "Punjabi",
    "ro" => "Romanian",
    "ru" => "Russian",
    "sm" => "Samoan",
    "gd" => "Scots Gaelic",
    "sr" => "Serbian",
    "st" => "Sesotho",
    "sn" => "Shona",
    "sd" => "Sindhi",
    "si" => "Sinhala",
    "sk" => "Slovak",
    "sl" => "Slovenian",
    "so" => "Somali",
    "su" => "Sundanese",
    "sw" => "Swahili",
    "sv" => "Swedish",
    "tg" => "Tajik",
    "ta" => "Tamil",
    "te" => "Telugu",
    "th" => "Thai",
    "tr" => "Turkish",
    "uk" => "Ukrainian",
    "ur" => "Urdu",
    "uz" => "Uzbek",
    "vi" => "Vietnamese",
    "cy" => "Welsh",
    "xh" => "Xhosa",
    "yi" => "Yiddish",
    "yo" => "Yoruba",
    "zu" => "Zulu"
];

$user = $tdata['user'];
?>
<div class="user-container">
    <div class="status-msg-modify"></div>
    <h1>Editar Perfil</h1>
    <form id="profileForm">
        <table class="form-table">
            <tr>
                <td><label for="username">Usuario:</label></td>
                <td><input type="text" id="username" name="username" required value="<?php echo $user['username'] ?? ''; ?>"></td>
            </tr>

            <tr>
                <td><label for="email">Email:</label></td>
                <td><input type="text" id="email" name="email" required value="<?php echo $user['email'] ?? ''; ?>"></td>
            </tr>

            <tr>
                <td><label for="currentPassword">Contraseña actual:</label></td>
                <td>
                    <div class="password-container">
                        <input type="password" id="currentPassword" name="currentPassword" required>
                        <span class="toggle-password">👁️</span>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label for="newPassword">Nueva contraseña:</label></td>
                <td>
                    <div class="password-container">
                        <input type="password" id="newPassword" name="newPassword">
                        <span class="toggle-password">👁️</span>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label for="confirmPassword">Confirmar contraseña:</label></td>
                <td>
                    <div class="password-container">
                        <input type="password" id="confirmPassword" name="confirmPassword">
                        <span class="toggle-password">👁️</span>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label for="timezone">Zona horaria:</label></td>
                <td>
                    <select id="timezone" name="timezone" required>
                        <?php foreach ($timezones as $tzValue => $tzLabel): ?>
                            <option value="<?php echo $tzValue; ?>" <?php echo (isset($user['timezone']) && $user['timezone'] === $tzValue) ? 'selected' : ''; ?>>
                                <?php echo $tzLabel; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td><label for="theme">Tema:</label></td>
                <td>
                    <select id="theme" name="theme" disabled required>
                        <option value="default" <?php echo (isset($user['theme']) && $user['theme'] === 'default') ? 'selected' : ''; ?>>default</option>

                    </select>
                </td>
            </tr>

            <tr>
                <td><label for="lang">Idioma:</label></td>
                <td>
                    <select id="lang" name="lang" required>
                        <?php foreach ($langs as $langValue => $langLabel): ?>
                            <option value="<?php echo $langValue; ?>" <?php echo (isset($user['lang']) && $user['lang'] === $langValue) ? 'selected' : ''; ?>>
                                <?php echo $langLabel; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <div class="btn-container">
            <button type="submit">Guardar Cambios</button>
        </div>
    </form>
</div>
