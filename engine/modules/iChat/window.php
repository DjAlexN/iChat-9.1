<?php


@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -20));
define('ENGINE_DIR', ROOT_DIR . '/engine');

include ENGINE_DIR . '/modules/iChat/data/config.php';
include_once ENGINE_DIR . '/modules/iChat/data/language.lng';

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set($config['date_adjust']);

if ($config['http_home_url'] == "") {
    
    $config['http_home_url'] = explode("engine/modules/iChat/window.php", $_SERVER['PHP_SELF']);
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

echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$config['charset']}" />
<title>{$chat_lang['window_title']}</title>
HTML;

echo "\n" . build_js(array(), $config) . "\n";

echo <<<HTML
<link media="screen" href="{$config['http_home_url']}templates/{$config['skin']}/iChat/css/window.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="loading-layer" style="display:none"><div id="loading-layer-text">{$lang['ajax_info']}</div></div>

<script language="javascript" type="text/javascript">
<!--
var dle_root       = '{$config['http_home_url']}';
var dle_admin      = '{$config['admin_path']}';
var dle_login_hash = '{$dle_login_hash}';
var dle_group      = {$member_id['user_group']};
var dle_skin       = '{$config['skin']}';
var dle_wysiwyg    = '{$config['allow_comments_wysiwyg']}';
var quick_wysiwyg  = '{$config['allow_quick_wysiwyg']}';
var dle_act_lang   = ["{$lang['p_yes']}", "{$lang['p_no']}", "{$lang['p_enter']}", "{$lang['p_cancel']}"];
var menu_short     = '{$lang['menu_short']}';
var menu_full      = '{$lang['menu_full']}';
var menu_profile   = '{$lang['menu_profile']}';
var menu_send      = '{$lang['menu_send']}';
var menu_uedit     = '{$lang['menu_uedit']}';
var dle_info       = '{$lang['p_info']}';
var dle_confirm    = '{$lang['p_confirm']}';
var dle_prompt     = '{$lang['p_prompt']}';
var dle_req_field  = '{$lang['comm_req_f']}';
var dle_del_agree  = '{$lang['news_delcom']}';
var dle_complaint  = '{$lang['add_to_complaint']}';
var dle_p_send     = '{$lang['p_send']}';
var dle_p_send_ok  = '{$lang['p_send_ok']}';
var dle_del_news   = '{$lang['news_delnews']}';\n
//-->
</script>
HTML;

$_POST['place'] = "window";

include ENGINE_DIR . '/modules/iChat/run.php';

echo <<<HTML
</body>
</html>
HTML;

?>