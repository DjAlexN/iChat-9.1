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
    
    $config['http_home_url'] = explode("engine/modules/iChat/ajax/edit.php", $_SERVER['PHP_SELF']);
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
    die("error");

if (!$user_group[$member_id['user_group']]['edit_allc']) {
    die("error");
}

$id = trim($_POST['id']);

@header("Content-type: text/html; charset=" . $config['charset']);

require_once ENGINE_DIR . '/classes/parse.class.php';

$parse              = new ParseFilter();
$parse->safe_mode   = true;
$parse->allow_url   = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

if ($_POST['action'] == "show" AND is_numeric($id)) {
    
    $row = $db->super_query("SELECT message FROM " . PREFIX . "_iChat WHERE id = '{$id}'");
    
    echo $parse->decodeBBCodes($row['message'], false);
    
}

if ($_POST['action'] == "save" AND is_numeric($id)) {
    
    $new_message = convert_unicode($_POST['new_message'], $config['charset']);
    
    $i         = 0;
    $ban_codes = explode(",", $chat_cfg['stop_bbcode']);
    foreach ($ban_codes as $ban_code) {
        $i++;
        $ban_code = trim($ban_code);
        if (stristr(strtolower(stripslashes($new_message)), $ban_code)) {
            $error = $chat_lang['bad'];
            break;
        }
    }
    
    if (dle_strlen(stripslashes($new_message), $config['charset']) > $chat_cfg['max_text']) {
        $error = $chat_lang['max'];
    }
    
    if (trim($_POST['new_message']) == '') {
        $error = $chat_lang['null'];
    }
	
	preg_match_all('/\[(b|i|u|s|code|quote|color=([^\]]+)|quote=([^\]]+))]/is', $new_message, $count_start_tag);
    preg_match_all('/\[\/(b|i|u|s|code|quote|color|quote)]/is', $new_message, $count_end_tag);
    
    if (count($count_start_tag[0]) != count($count_end_tag[0]))
        $error = $chat_lang['stop_tags'];
    
    if (!$error) {
        $new_message = $db->safesql($parse->BB_Parse($parse->process($new_message), false));
        
        $smilies_arr = explode(",", $chat_cfg['smiles']);
        foreach ($smilies_arr as $smile) {
            $smile     = trim($smile);
            $find[]    = "':{$smile}:'";
            $replace[] = "<!--smile:{$smile}--><img style=\"vertical-align: middle;border: none;\" alt=\"{$smile}\" src=\"" . $config['http_home_url'] . "engine/modules/iChat/emoticons/{$smile}.gif\" /><!--/smile-->";
        }
        
        $new_message = preg_replace($find, $replace, $new_message);
        
        //* Automatyczne przycinanie długich słów
        if (intval($chat_cfg['max_word'])) {
            
            $new_message = preg_split('((>)|(<))', $new_message, -1, PREG_SPLIT_DELIM_CAPTURE);
            $n           = count($new_message);
            
            for ($i = 0; $i < $n; $i++) {
                if ($new_message[$i] == "<") {
                    $i++;
                    continue;
                }
                
                $new_message[$i] = preg_replace("#([^\s\n\r]{" . intval($chat_cfg['max_word']) . "})#i", "\\1<br />", $new_message[$i]);
            }
            
            $new_message = join("", $new_message);
            
        }
        
        $db->query("UPDATE " . PREFIX . "_iChat SET message='{$new_message}' WHERE id = '{$id}'");
        
        clear_cache('iChat_');
        
    }
    
    $config['allow_cache'] = 1;
    
    switch ($_POST['place']) {
        
        case "site":
            $Messages = dle_cache("iChat", $config['skin'], true);
            break;
        
        case "window":
            $Messages = dle_cache("iChat_window", $config['skin'], true);
            break;
        
        case "history":
            $_POST['page'] = ($_SESSION['page'] >= 1) ? $_SESSION['page'] : 1;
            $Messages      = dle_cache("iChat_history_" . $_POST['page'], $config['skin'], true);
            break;
            
    }
    
    include ENGINE_DIR . '/modules/iChat/build.php';
    
    if ($_POST['place'] != "history")
        $_SESSION['hash_messages_' . $_POST['place']] = md5($Messages);
    
    echo $Messages;   
    
    if (!$error)
        $js_c = "document.getElementById('iChat_form').message.value = '';";
    else
        $js_c = "DLEalert('" . $error . "', '" . $chat_lang['title'] . "')";
    
    echo '<script language="JavaScript" type="text/javascript">' . $js_c . '</script>';
    
}

?>