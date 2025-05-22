<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
$user = $tdata['user'];
?>
<div class="user-container">
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
                        <input type="password" id="newPassword" name="newPassword" required>
                        <span class="toggle-password">👁️</span>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label for="confirmPassword">Confirmar contraseña:</label></td>
                <td>
                    <div class="password-container">
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <span class="toggle-password">👁️</span>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label for="timezone">Zona horaria:</label></td>
                <td>
                    <select id="timezone" name="timezone" required>
                        <option value="Pacific/Midway">(UTC-11:00) Midway Island, American Samoa</option>
                        <option value="Pacific/Honolulu">(UTC-10:00) Hawaii</option>
                        <option value="America/Anchorage">(UTC-08:00) Alaska</option>
                        <option value="America/Los_Angeles">(UTC-07:00) Pacific Time (US and Canada)</option>
                        <option value="America/Tijuana">(UTC-07:00) Baja California</option>
                        <option value="America/Phoenix">(UTC-07:00) Arizona</option>
                        <option value="America/Denver">(UTC-06:00) Mountain Time (US and Canada)</option>
                        <option value="America/Chihuahua">(UTC-06:00) Chihuahua, La Paz, Mazatlan</option>
                        <option value="America/Belize">(UTC-06:00) Central America</option>
                        <option value="America/Regina">(UTC-06:00) Saskatchewan</option>
                        <option value="America/Chicago">(UTC-05:00) Central Time (US and Canada)</option>
                        <option value="America/Mexico_City">(UTC-05:00) Guadalajara, Mexico City, Monterrey</option>
                        <option value="America/Bogota">(UTC-05:00) Bogota, Lima, Quito</option>
                        <option value="America/Jamaica">(UTC-05:00) Kingston, George Town</option>
                        <option value="America/Manaus">(UTC-04:00) Georgetown, La Paz, Manaus, San Juan</option>
                        <option value="America/Cuiaba">(UTC-04:00) Cuiaba</option>
                        <option value="America/New_York">(UTC-04:00) Eastern Time (US and Canada)</option>
                        <option value="America/Indiana/Indianapolis">(UTC-04:00) Indiana (East)</option>
                        <option value="America/Caracas">(UTC-04:30) Caracas</option>
                        <option value="America/Halifax">(UTC-03:00) Atlantic Time (Canada)</option>
                        <option value="America/Asuncion">(UTC-03:00) Asuncion</option>
                        <option value="America/Sao_Paulo">(UTC-03:00) Brasilia</option>
                        <option value="America/Buenos_Aires">(UTC-03:00) Buenos Aires</option>
                        <option value="America/Cayenne">(UTC-03:00) Cayenne, Fortaleza</option>
                        <option value="America/Montevideo">(UTC-03:00) Montevideo</option>
                        <option value="America/Bahia">(UTC-03:00) Salvador</option>
                        <option value="America/Santiago">(UTC-03:00) Santiago</option>
                        <option value="America/St_Johns">(UTC-02:30) Newfoundland and Labrador</option>
                        <option value="America/Godthab">(UTC-02:00) Greenland</option>
                        <option value="America/Noronha">(UTC-02:00) Mid-Atlantic</option>
                        <option value="Atlantic/Cape_Verde">(UTC-01:00) Cape Verde Islands</option>
                        <option value="Africa/Monrovia">(UTC+00:00) Monrovia, Reykjavik</option>
                        <option value="Atlantic/Azores">(UTC+00:00) Azores</option>
                        <option value="Europe/London">(UTC+01:00) Dublin, Edinburgh, Lisbon, London</option>
                        <option value="Africa/Casablanca">(UTC+01:00) Casablanca</option>
                        <option value="Africa/Algiers">(UTC+01:00) West Central Africa</option>
                        <option value="Europe/Amsterdam">(UTC+02:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna</option>
                        <option value="Europe/Belgrade">(UTC+02:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague</option>
                        <option value="Europe/Brussels">(UTC+02:00) Brussels, Copenhagen, Madrid, Paris</option>
                        <option value="Europe/Warsaw">(UTC+02:00) Sarajevo, Skopje, Warsaw, Zagreb</option>
                        <option value="Africa/Cairo">(UTC+02:00) Cairo</option>
                        <option value="Africa/Harare">(UTC+02:00) Harare, Pretoria</option>
                        <option value="Europe/Kaliningrad">(UTC+02:00) Kaliningrad</option>
                        <option value="Africa/Tripoli">(UTC+02:00) Tripoli</option>
                        <option value="Africa/Windhoek">(UTC+02:00) Windhoek</option>
                        <option value="Europe/Athens">(UTC+03:00) Athens, Bucharest</option>
                        <option value="Asia/Beirut">(UTC+03:00) Beirut</option>
                        <option value="Asia/Damascus">(UTC+03:00) Damascus</option>
                        <option value="EET">(UTC+03:00) Eastern Europe</option>
                        <option value="Europe/Helsinki">(UTC+03:00) Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius</option>
                        <option value="Asia/Istanbul">(UTC+03:00) Istanbul</option>
                        <option value="Asia/Jerusalem">(UTC+03:00) Jerusalem</option>
                        <option value="Asia/Amman">(UTC+03:00) Amman</option>
                        <option value="Asia/Baghdad">(UTC+03:00) Baghdad</option>
                        <option value="Asia/Kuwait">(UTC+03:00) Kuwait, Riyadh</option>
                        <option value="Europe/Minsk">(UTC+03:00) Minsk</option>
                        <option value="Europe/Moscow">(UTC+03:00) Moscow, St. Petersburg, Volgograd</option>
                        <option value="Africa/Nairobi">(UTC+03:00) Nairobi</option>
                        <option value="Asia/Tehran">(UTC+03:30) Tehran</option>
                        <option value="Asia/Muscat">(UTC+04:00) Abu Dhabi, Muscat</option>
                        <option value="Europe/Samara">(UTC+04:00) Izhevsk, Samara</option>
                        <option value="Indian/Mauritius">(UTC+04:00) Port Louis</option>
                        <option value="Asia/Tbilisi">(UTC+04:00) Tbilisi</option>
                        <option value="Asia/Yerevan">(UTC+04:00) Yerevan</option>
                        <option value="Asia/Baku">(UTC+05:00) Baku</option>
                        <option value="Asia/Kabul">(UTC+04:30) Kabul</option>
                        <option value="Asia/Yekaterinburg">(UTC+05:00) Ekaterinburg</option>
                        <option value="Asia/Tashkent">(UTC+05:00) Tashkent, Ashgabat</option>
                        <option value="Asia/Karachi">(UTC+05:00) Islamabad, Karachi</option>
                        <option value="Asia/Kolkata">(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
                        <option value="Asia/Colombo">(UTC+05:30) Sri Jayawardenepura</option>
                        <option value="Asia/Katmandu">(UTC+05:45) Kathmandu</option>
                        <option value="Asia/Almaty">(UTC+06:00) Astana</option>
                        <option value="Asia/Dhaka">(UTC+06:00) Dhaka</option>
                        <option value="Asia/Novosibirsk">(UTC+06:00) Novosibirsk</option>
                        <option value="Asia/Rangoon">(UTC+06:30) Yangon (Rangoon)</option>
                        <option value="Asia/Bangkok">(UTC+07:00) Bangkok, Hanoi, Jakarta</option>
                        <option value="Asia/Krasnoyarsk">(UTC+07:00) Krasnoyarsk</option>
                        <option value="Asia/Chongqing">(UTC+08:00) Beijing, Chongqing, Hong Kong SAR, Urumqi</option>
                        <option value="Asia/Irkutsk">(UTC+08:00) Irkutsk</option>
                        <option value="Asia/Kuala_Lumpur">(UTC+08:00) Kuala Lumpur, Singapore</option>
                        <option value="Australia/Perth">(UTC+08:00) Perth</option>
                        <option value="Asia/Taipei">(UTC+08:00) Taipei</option>
                        <option value="Asia/Ulaanbaatar">(UTC+08:00) Ulaanbaatar</option>
                        <option value="Asia/Tokyo">(UTC+09:00) Osaka, Sapporo, Tokyo</option>
                        <option value="Asia/Seoul">(UTC+09:00) Seoul</option>
                        <option value="Asia/Yakutsk">(UTC+09:00) Yakutsk</option>
                        <option value="Australia/Darwin">(UTC+09:30) Darwin</option>
                        <option value="Australia/Brisbane">(UTC+10:00) Brisbane</option>
                        <option value="Pacific/Guam">(UTC+10:00) Guam, Port Moresby</option>
                        <option value="Asia/Magadan">(UTC+10:00) Magadan</option>
                        <option value="Asia/Vladivostok">(UTC+10:00) Vladivostok, Magadan</option>
                        <option value="Australia/Adelaide">(UTC+10:30) Adelaide</option>
                        <option value="Australia/Canberra">(UTC+11:00) Canberra, Melbourne, Sydney</option>
                        <option value="Australia/Hobart">(UTC+11:00) Hobart</option>
                        <option value="Asia/Srednekolymsk">(UTC+11:00) Chokirdakh</option>
                        <option value="Pacific/Guadalcanal">(UTC+11:00) Solomon Islands, New Caledonia</option>
                        <option value="Asia/Anadyr">(UTC+12:00) Anadyr, Petropavlovsk-Kamchatsky</option>
                        <option value="Pacific/Fiji">(UTC+12:00) Fiji Islands, Kamchatka, Marshall Islands</option>
                        <option value="Pacific/Auckland">(UTC+13:00) Auckland, Wellington</option>
                        <option value="Pacific/Tongatapu">(UTC+13:00) Nuku'alofa</option>
                        <option value="Pacific/Apia">(UTC+14:00) Samoa</option>
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
                        <option value="en" <?php echo (isset($user['lang']) && $user['lang'] === 'en') ? 'selected' : ''; ?>>English</option>
                        <option value="es" <?php echo (isset($user['lang']) && $user['lang'] === 'es') ? 'selected' : ''; ?>>Spanish</option>
                        <option value=af>Afrikaans</option>
                        <option value=sq>Albanian</option>
                        <option value=am>Amharic</option>
                        <option value=ar>Arabic</option>
                        <option value=hy>Armenian</option>
                        <option value=az>Azerbaijani</option>
                        <option value=eu>Basque</option>
                        <option value=be>Belarusian</option>
                        <option value=bn>Bengali</option>
                        <option value=bs>Bosnian</option>
                        <option value=bg>Bulgarian</option>
                        <option value=ca>Catalan</option>
                        <option value=ceb>Cebuano</option>
                        <option value=ny>Chichewa</option>
                        <option value=zh-CN>Chinese</option>
                        <option value=co>Corsican</option>
                        <option value=hr>Croatian</option>
                        <option value=cs>Czech</option>
                        <option value=da>Danish</option>
                        <option value=nl>Dutch</option>
                        <option value=eo>Esperanto</option>
                        <option value=et>Estonian</option>
                        <option value=tl>Filipino</option>
                        <option value=fi>Finnish</option>
                        <option value=fr>French</option>
                        <option value=fy>Frisian</option>
                        <option value=gl>Galician</option>
                        <option value=ka>Georgian</option>
                        <option value=de>German</option>
                        <option value=el>Greek</option>
                        <option value=gu>Gujarati</option>
                        <option value=ht>Haitian Creole</option>
                        <option value=ha>Hausa</option>
                        <option value=haw>Hawaiian</option>
                        <option value=iw>Hebrew</option>
                        <option value=hi>Hindi</option>
                        <option value=hmn>Hmong</option>
                        <option value=hu>Hungarian</option>
                        <option value=is>Icelandic</option>
                        <option value=ig>Igbo</option>
                        <option value=id>Indonesian</option>
                        <option value=ga>Irish</option>
                        <option value=it>Italian</option>
                        <option value=ja>Japanese</option>
                        <option value=jw>Javanese</option>
                        <option value=kn>Kannada</option>
                        <option value=kk>Kazakh</option>
                        <option value=km>Khmer</option>
                        <option value=ko>Korean</option>
                        <option value=ku>Kurdish (Kurmanji)</option>
                        <option value=ky>Kyrgyz</option>
                        <option value=lo>Lao</option>
                        <option value=la>Latin</option>
                        <option value=lv>Latvian</option>
                        <option value=lt>Lithuanian</option>
                        <option value=lb>Luxembourgish</option>
                        <option value=mk>Macedonian</option>
                        <option value=mg>Malagasy</option>
                        <option value=ms>Malay</option>
                        <option value=ml>Malayalam</option>
                        <option value=mt>Maltese</option>
                        <option value=mi>Maori</option>
                        <option value=mr>Marathi</option>
                        <option value=mn>Mongolian</option>
                        <option value=my>Myanmar (Burmese)</option>
                        <option value=ne>Nepali</option>
                        <option value=no>Norwegian</option>
                        <option value=ps>Pashto</option>
                        <option value=fa>Persian</option>
                        <option value=pl>Polish</option>
                        <option value=pt>Portuguese</option>
                        <option value=pa>Punjabi</option>
                        <option value=ro>Romanian</option>
                        <option value=ru>Russian</option>
                        <option value=sm>Samoan</option>
                        <option value=gd>Scots Gaelic</option>
                        <option value=sr>Serbian</option>
                        <option value=st>Sesotho</option>
                        <option value=sn>Shona</option>
                        <option value=sd>Sindhi</option>
                        <option value=si>Sinhala</option>
                        <option value=sk>Slovak</option>
                        <option value=sl>Slovenian</option>
                        <option value=so>Somali</option>
                        <option value=su>Sundanese</option>
                        <option value=sw>Swahili</option>
                        <option value=sv>Swedish</option>
                        <option value=tg>Tajik</option>
                        <option value=ta>Tamil</option>
                        <option value=te>Telugu</option>
                        <option value=th>Thai</option>
                        <option value=tr>Turkish</option>
                        <option value=uk>Ukrainian</option>
                        <option value=ur>Urdu</option>
                        <option value=uz>Uzbek</option>
                        <option value=vi>Vietnamese</option>
                        <option value=cy>Welsh</option>
                        <option value=xh>Xhosa</option>
                        <option value=yi>Yiddish</option>
                        <option value=yo>Yoruba</option>
                        <option value=zu>Zulu</option>
                    </select>
                </td>
            </tr>
        </table>

        <div class="btn-container">
            <button type="submit">Guardar Cambios</button>
        </div>
    </form>
</div>

<script>
    // Función para mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(function(element) {
        element.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '👁️';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword && newPassword !== confirmPassword) {
            alert('Las contraseñas no coinciden');
            return;
        }

        // Aquí iría el código para enviar los datos al servidor
        alert('Cambios guardados correctamente');
        // Ejemplo: fetch('/api/update-profile', { method: 'POST', body: new FormData(this) });
    });


    /*
    fetch('/api/user-profile')
        .then(response => response.json())
        .then(data => {
            document.getElementById('username').value = data.username;
            document.getElementById('timezone').value = data.timezone;
            document.getElementById('theme').value = data.theme;
            document.getElementById('lang').value = data.lang;
        });
    */
</script>
