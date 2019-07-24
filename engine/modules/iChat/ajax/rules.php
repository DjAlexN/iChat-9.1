<?php

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

if ($_SERVER['HTTP_X_REQUESTED_WITH'] != "XMLHttpRequest")
    die('Only for AJAX requests!');

if (file_exists(ENGINE_DIR . '/classes/plugins.class.php')) {
    require_once ENGINE_DIR . '/classes/plugins.class.php';
}

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -25));
define('ENGINE_DIR', ROOT_DIR . '/engine');

include_once ENGINE_DIR . '/modules/iChat/data/language.lng';

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set($config['date_adjust']);

if ($config['http_home_url'] == "") {
    
    $config['http_home_url'] = explode("engine/modules/iChat/ajax/rules.php", $_SERVER['PHP_SELF']);
    $config['http_home_url'] = reset($config['http_home_url']);
    $config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
    
}

require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$_COOKIE['dle_skin'] = trim(totranslit($_COOKIE['dle_skin'], false, false));

if ($_COOKIE['dle_skin']) {
    if (@is_dir(ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'])) {
        $config['skin'] = $_COOKIE['dle_skin'];
    }
}

$rules = file_get_contents(ROOT_DIR . '/templates/' . $config['skin'] . '/iChat/rules.tpl');

@header("Content-type: text/html; charset=" . $config['charset']);

echo "<div id='rules' title='{$chat_lang['rules']}' style='display:none'>{$rules}</div>";

?>