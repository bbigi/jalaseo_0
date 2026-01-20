<?php

namespace App\Helpers;

class CountryHelper
{
    public static function getCountries(): array
    {
        return [
            // Americas
            ['code' => 'US', 'name' => 'United States', 'flag' => '🇺🇸'],
            ['code' => 'CA', 'name' => 'Canada', 'flag' => '🇨🇦'],
            ['code' => 'MX', 'name' => 'Mexico', 'flag' => '🇲🇽'],
            ['code' => 'BR', 'name' => 'Brazil', 'flag' => '🇧🇷'],
            ['code' => 'AR', 'name' => 'Argentina', 'flag' => '🇦🇷'],
            ['code' => 'CL', 'name' => 'Chile', 'flag' => '🇨🇱'],
            ['code' => 'CO', 'name' => 'Colombia', 'flag' => '🇨🇴'],

            // Europe
            ['code' => 'GB', 'name' => 'United Kingdom', 'flag' => '🇬🇧'],
            ['code' => 'DE', 'name' => 'Germany', 'flag' => '🇩🇪'],
            ['code' => 'FR', 'name' => 'France', 'flag' => '🇫🇷'],
            ['code' => 'IT', 'name' => 'Italy', 'flag' => '🇮🇹'],
            ['code' => 'ES', 'name' => 'Spain', 'flag' => '🇪🇸'],
            ['code' => 'NL', 'name' => 'Netherlands', 'flag' => '🇳🇱'],
            ['code' => 'BE', 'name' => 'Belgium', 'flag' => '🇧🇪'],
            ['code' => 'CH', 'name' => 'Switzerland', 'flag' => '🇨🇭'],
            ['code' => 'AT', 'name' => 'Austria', 'flag' => '🇦🇹'],
            ['code' => 'SE', 'name' => 'Sweden', 'flag' => '🇸🇪'],
            ['code' => 'NO', 'name' => 'Norway', 'flag' => '🇳🇴'],
            ['code' => 'DK', 'name' => 'Denmark', 'flag' => '🇩🇰'],
            ['code' => 'FI', 'name' => 'Finland', 'flag' => '🇫🇮'],
            ['code' => 'PL', 'name' => 'Poland', 'flag' => '🇵🇱'],
            ['code' => 'PT', 'name' => 'Portugal', 'flag' => '🇵🇹'],
            ['code' => 'GR', 'name' => 'Greece', 'flag' => '🇬🇷'],
            ['code' => 'CZ', 'name' => 'Czech Republic', 'flag' => '🇨🇿'],
            ['code' => 'RO', 'name' => 'Romania', 'flag' => '🇷🇴'],
            ['code' => 'HU', 'name' => 'Hungary', 'flag' => '🇭🇺'],

            // Asia
            ['code' => 'ID', 'name' => 'Indonesia', 'flag' => '🇮🇩'],
            ['code' => 'MY', 'name' => 'Malaysia', 'flag' => '🇲🇾'],
            ['code' => 'SG', 'name' => 'Singapore', 'flag' => '🇸🇬'],
            ['code' => 'TH', 'name' => 'Thailand', 'flag' => '🇹🇭'],
            ['code' => 'VN', 'name' => 'Vietnam', 'flag' => '🇻🇳'],
            ['code' => 'PH', 'name' => 'Philippines', 'flag' => '🇵🇭'],
            ['code' => 'JP', 'name' => 'Japan', 'flag' => '🇯🇵'],
            ['code' => 'KR', 'name' => 'South Korea', 'flag' => '🇰🇷'],
            ['code' => 'CN', 'name' => 'China', 'flag' => '🇨🇳'],
            ['code' => 'IN', 'name' => 'India', 'flag' => '🇮🇳'],
            ['code' => 'PK', 'name' => 'Pakistan', 'flag' => '🇵🇰'],
            ['code' => 'BD', 'name' => 'Bangladesh', 'flag' => '🇧🇩'],
            ['code' => 'TW', 'name' => 'Taiwan', 'flag' => '🇹🇼'],
            ['code' => 'HK', 'name' => 'Hong Kong', 'flag' => '🇭🇰'],

            // Middle East
            ['code' => 'AE', 'name' => 'United Arab Emirates', 'flag' => '🇦🇪'],
            ['code' => 'SA', 'name' => 'Saudi Arabia', 'flag' => '🇸🇦'],
            ['code' => 'TR', 'name' => 'Turkey', 'flag' => '🇹🇷'],
            ['code' => 'IL', 'name' => 'Israel', 'flag' => '🇮🇱'],
            ['code' => 'EG', 'name' => 'Egypt', 'flag' => '🇪🇬'],

            // Oceania
            ['code' => 'AU', 'name' => 'Australia', 'flag' => '🇦🇺'],
            ['code' => 'NZ', 'name' => 'New Zealand', 'flag' => '🇳🇿'],

            // Africa
            ['code' => 'ZA', 'name' => 'South Africa', 'flag' => '🇿🇦'],
            ['code' => 'NG', 'name' => 'Nigeria', 'flag' => '🇳🇬'],
            ['code' => 'KE', 'name' => 'Kenya', 'flag' => '🇰🇪'],
            ['code' => 'MA', 'name' => 'Morocco', 'flag' => '🇲🇦'],
        ];
    }

    public static function getLanguages(): array
    {
        return [
            ['code' => 'en_US', 'name' => 'English (US)', 'flag' => '🇺🇸'],
            ['code' => 'en_GB', 'name' => 'English (UK)', 'flag' => '🇬🇧'],
            ['code' => 'en_AU', 'name' => 'English (Australia)', 'flag' => '🇦🇺'],
            ['code' => 'id_ID', 'name' => 'Bahasa Indonesia (Indonesian)', 'flag' => '🇮🇩'],
            ['code' => 'ms_MY', 'name' => 'Bahasa Melayu (Malay)', 'flag' => '🇲🇾'],
            ['code' => 'es_ES', 'name' => 'Español (España) / Spanish', 'flag' => '🇪🇸'],
            ['code' => 'es_MX', 'name' => 'Español (México) / Spanish', 'flag' => '🇲🇽'],
            ['code' => 'fr_FR', 'name' => 'Français (French)', 'flag' => '🇫🇷'],
            ['code' => 'de_DE', 'name' => 'Deutsch (German)', 'flag' => '🇩🇪'],
            ['code' => 'it_IT', 'name' => 'Italiano (Italian)', 'flag' => '🇮🇹'],
            ['code' => 'pt_BR', 'name' => 'Português (Brasil) / Portuguese', 'flag' => '🇧🇷'],
            ['code' => 'pt_PT', 'name' => 'Português (Portugal) / Portuguese', 'flag' => '🇵🇹'],
            ['code' => 'nl_NL', 'name' => 'Nederlands (Dutch)', 'flag' => '🇳🇱'],
            ['code' => 'pl_PL', 'name' => 'Polski (Polish)', 'flag' => '🇵🇱'],
            ['code' => 'ru_RU', 'name' => 'Русский (Russian)', 'flag' => '🇷🇺'],
            ['code' => 'ja_JP', 'name' => '日本語 (Japanese)', 'flag' => '🇯🇵'],
            ['code' => 'ko_KR', 'name' => '한국어 (Korean)', 'flag' => '🇰🇷'],
            ['code' => 'zh_CN', 'name' => '简体中文 (Chinese - Simplified)', 'flag' => '🇨🇳'],
            ['code' => 'zh_TW', 'name' => '繁體中文 (Chinese - Traditional)', 'flag' => '🇹🇼'],
            ['code' => 'th_TH', 'name' => 'ไทย (Thai)', 'flag' => '🇹🇭'],
            ['code' => 'vi_VN', 'name' => 'Tiếng Việt (Vietnamese)', 'flag' => '🇻🇳'],
            ['code' => 'ar_SA', 'name' => 'العربية (Arabic)', 'flag' => '🇸🇦'],
            ['code' => 'tr_TR', 'name' => 'Türkçe (Turkish)', 'flag' => '🇹🇷'],
            ['code' => 'sv_SE', 'name' => 'Svenska (Swedish)', 'flag' => '🇸🇪'],
            ['code' => 'da_DK', 'name' => 'Dansk (Danish)', 'flag' => '🇩🇰'],
            ['code' => 'fi_FI', 'name' => 'Suomi (Finnish)', 'flag' => '🇫🇮'],
            ['code' => 'no_NO', 'name' => 'Norsk (Norwegian)', 'flag' => '🇳🇴'],
            ['code' => 'cs_CZ', 'name' => 'Čeština (Czech)', 'flag' => '🇨🇿'],
            ['code' => 'el_GR', 'name' => 'Ελληνικά (Greek)', 'flag' => '🇬🇷'],
            ['code' => 'he_IL', 'name' => 'עברית (Hebrew)', 'flag' => '🇮🇱'],
            ['code' => 'hi_IN', 'name' => 'हिन्दी (Hindi)', 'flag' => '🇮🇳'],
        ];
    }
}
