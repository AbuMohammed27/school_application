<?php
session_start();

$supported_languages = ['en', 'fr']; // Add more as needed
$default_language = 'en';

// Set language from session or browser
if (!isset($_SESSION['lang'])) {
    $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    $_SESSION['lang'] = in_array($browser_lang, $supported_languages) ? $browser_lang : $default_language;
}

// Language switcher HTML
function languageSwitcher() {
    global $supported_languages;
    echo '<div class="language-switcher">';
    foreach ($supported_languages as $lang) {
        echo '<a href="?lang=' . $lang . '">' . strtoupper($lang) . '</a> ';
    }
    echo '</div>';
}
?>