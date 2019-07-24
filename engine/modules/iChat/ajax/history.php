<?php

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -25));
define('ENGINE_DIR', ROOT_DIR . '/engine');

if (file_exists(ENGINE_DIR . '/classes/plugins.class.php')) {
    require_once ENGINE_DIR . '/classes/plugins.class.php';
}

include ENGINE_DIR . '/modules/iChat/data/config.php';
include_once ENGINE_DIR . '/modules/iChat/data/language.lng';

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set($config['date_adjust']);

if ($config['http_home_url'] == "") {
    
    $config['http_home_url'] = explode("engine/modules/iChat/ajax/history.php", $_SERVER['PHP_SELF']);
    $config['http_home_url'] = reset($config['http_home_url']);
    $config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
    
}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$_COOKIE['dle_skin'] = trim(totranslit($_COOKIE['dle_skin'], false, false));

if ($_COOKIE['dle_skin']) {
    if (@is_dir(ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'])) {
        $config['skin'] = $_COOKIE['dle_skin'];
    }
}

if ($config["lang_" . $config['skin']]) {
    
    if (file_exists(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng')) {
        @include_once(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng');
    } else
        die("Language file not found");
    
} else {
    
    include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';
    
}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR . '/modules/sitelogin.php';

//################# Definiowanie grup użytkowników
$user_group = get_vars("usergroup");

if (!$user_group) {
    $user_group = array();
    
    $db->query("SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC");
    
    while ($row = $db->get_row()) {
        
        $user_group[$row['id']] = array();
        
        foreach ($row as $key => $value) {
            $user_group[$row['id']][$key] = stripslashes($value);
        }
        
    }
    set_vars("usergroup", $user_group);
    $db->free();
}

if (!$is_logged)
    $member_id['user_group'] = 5;

if (!in_array($member_id['user_group'], $allow_group = explode(',', $chat_cfg['history']))) {
    die('Error');
}

@header("Content-type: text/html; charset=" . $config['charset']);

$config['allow_cache'] = 1;

$_POST['page'] = ($_POST['page'] >= 1) ? $_POST['page'] : 1;

$Messages = dle_cache("iChat_history_" . $_POST['page'], $config['skin'], true);

$_POST['place'] = "history";

include ENGINE_DIR . '/modules/iChat/build.php';

echo "<div id='history' title='{$chat_lang['history']}' style='display:none;overflow:auto;'><div id='history_c'>{$Messages}</div></div>";

?>