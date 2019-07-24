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
    
    $config['http_home_url'] = explode("engine/modules/iChat/ajax/add.php", $_SERVER['PHP_SELF']);
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

if (!in_array($member_id['user_group'], $allow_group = explode(',', $chat_cfg['group_msg'])))
    die("error");

require_once ENGINE_DIR . '/classes/parse.class.php';

$parse              = new ParseFilter();
$parse->safe_mode   = true;
$parse->allow_url   = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

$_POST['message'] = trim($_POST['message']);

$foto_guest = $config['http_home_url'] . 'templates/' . $config['skin'] . "/iChat/img/guest.png";

$not_allow_symbol = array(
    "\x22",
    "\x60",
    "\t",
    '\n',
    '\r',
    "\n",
    "\r",
    '\\',
    ",",
    "/",
    "¬",
    "#",
    ";",
    ":",
    "~",
    "[",
    "]",
    "{",
    "}",
    ")",
    "(",
    "*",
    "^",
    "%",
    "$",
    "<",
    ">",
    "?",
    "!",
    '"',
    "'"
);

$message = convert_unicode($_POST['message'], $config['charset']);

if (($member_id['user_group'] != 1)) {
    $chat_cfg['stop_bbcode'] = $chat_cfg['stop_bbcode'] . ",[admin],[/admin]";
}

if (!$user_group[$member_id['user_group']]['allow_url']) {
    $chat_cfg['stop_bbcode'] = $chat_cfg['stop_bbcode'] . ",[url=,[/url],[leech=,[/leech]";
}

$i         = 0;
$ban_codes = explode(",", $chat_cfg['stop_bbcode']);
foreach ($ban_codes as $ban_code) {
    $i++;
    $ban_code = trim($ban_code);
    if (stristr(strtolower(stripslashes($message)), $ban_code)) {
        $error = $chat_lang['bad'];
        break;
    }
}

$ban_names = explode(",", $chat_cfg['no_access']);
foreach ($ban_names as $ban_name) {
    $ban_name = trim(strtolower($ban_name));
    if (strtolower($member_id['name']) == $ban_name) {
        $error = $chat_lang['no_access'];
        break;
    }
}

if (dle_strlen(stripslashes($message), $config['charset']) > $chat_cfg['max_text']) {
    $error = $chat_lang['max'];
}

if ($_POST['message'] == '' OR $member_id['banned'] == "yes") {
    $error = $chat_lang['null'];
}

// Ochrona przed spamem
if ($member_id['user_group'] > 2 and intval($chat_cfg['stop_flood']) and !$error) {
    if (flooder($_IP, $chat_cfg['stop_flood']) == TRUE) {
        $error = $chat_lang['flood'];
    }
}

preg_match_all('/\[(b|i|u|s|code|quote|color=([^\]]+)|quote=([^\]]+))]/is', $message, $count_start_tag);
preg_match_all('/\[\/(b|i|u|s|code|quote|color|quote)]/is', $message, $count_end_tag);

if (count($count_start_tag[0]) != count($count_end_tag[0]))
    $error = $chat_lang['stop_tags'];

if (md5($_POST['message']) == md5($_SESSION['last_message'])) {
    $error = $chat_lang['copy'];
}

if (($member_id['user_group'] == 1)) {
    if (@file_exists(ROOT_DIR . '/iChat_install.php')) {
        $error = $chat_lang['install_in'];
    }
}

if (!$error) {
    
    $_SESSION['last_message'] = $_POST['message'];
    
    $_TIME = time();
    $_IP   = $db->safesql($_SERVER['REMOTE_ADDR']);
    
    $name = $chat_cfg['guest_name'];
    if ($chat_cfg['guest_name_suffix']) {
        $ip_pref = explode(".", $_IP);
        $name .= "_" . $ip_pref[3];
    }
    
    function iChat_build_leech($matches)
    {
		$tytul = "[Link]";
        return $matches[1] . "[leech=" . $matches[2] . "]" . $tytul . "[/leech]";
    }
    
    $message = preg_replace_callback("#(^|\s|>)((http|https|ftp|magnet)://(\w+[^\s\[\]\<]+))#i", "iChat_build_leech", $message);
    
    $message = $db->safesql($parse->BB_Parse($parse->process($message), false));
    
    $smilies_arr = explode(",", $chat_cfg['smiles']);
    foreach ($smilies_arr as $smile) {
        $smile     = trim($smile);
        $find[]    = "':{$smile}:'";
        $replace[] = "<!--smile:{$smile}--><img style=\"vertical-align: middle;border: none;\" alt=\"{$smile}\" src=\"" . $config['http_home_url'] . "engine/modules/iChat/emoticons/{$smile}.gif\" /><!--/smile-->";
    }
    
    $message = preg_replace($find, $replace, $message);
    
    //* Automatyczny transfer długich słów
    if (intval($chat_cfg['max_word']) AND !$error) {
        
        $message = preg_split('((>)|(<))', $message, -1, PREG_SPLIT_DELIM_CAPTURE);
        $n       = count($message);
        
        for ($i = 0; $i < $n; $i++) {
            if ($message[$i] == "<") {
                $i++;
                continue;
            }
            
            $message[$i] = preg_replace("#([^\s\n\r]{" . intval($chat_cfg['max_word']) . "})#i", "\\1<br />", $message[$i]);
        }
        
        $message = join("", $message);
        
    }
    
    if ($chat_cfg['word_filter_status']) {
        function iChat_word_filter($source)
        {
            global $config, $chat_cfg, $_TIME;
            if ($chat_cfg['word_filter_arr']) {
                $chat_words = explode(",", $chat_cfg['word_filter_arr']);
                foreach ($chat_words as $w) {
                    $w           = trim($w);
                    $len         = dle_strlen($w, $config['charset']);
                    $w2          = str_repeat("*", $len);
                    $all_words[] = "{$TIME}|{$w}|{$w2}|0|0|0|0||";
                }
            }
            
            foreach ($all_words as $k => $v) {
                $v = explode("|", $v);
                if (mb_stripos($source, $v[1], 0, $config['charset']) !== false) {
                    $source    = str_ireplace($v[1], "{$k}", $source);
                    $replace[] = array(
                        $k,
                        $v[1],
                        $v[2]
                    );
                }
            }
            foreach ($replace as $v)
                $source = str_replace("{$v[0]}", "{$v[2]}", $source);
            
            return $source;
        }
        
        $message = iChat_word_filter($message);
        
    }
    
    if ($chat_cfg['stop_flood']) {
        $db->query("INSERT INTO " . PREFIX . "_flood (id, ip, flag) values ('{$_TIME}', '{$_IP}', '1')");
    }
    
    if ($is_logged)
        $db->query("INSERT INTO " . PREFIX . "_iChat (date, author, message, ip, user_group, foto) values ('{$_TIME}', '{$db->safesql($member_id['name'])}', '{$message}', '{$_IP}', '{$member_id['user_group']}', '{$member_id['foto']}')");
    else
        $db->query("INSERT INTO " . PREFIX . "_iChat (date, author, message, ip, user_group, foto) values ('{$_TIME}', '{$db->safesql($name)}', '{$message}', '{$_IP}', '5', '{$foto_guest}')");
    
    clear_cache('iChat_');
    
}

$config['allow_cache'] = 1;

if ($_POST['place'] == 'site')
    $Messages = dle_cache("iChat", $config['skin'], true);
if ($_POST['place'] == 'window')
    $Messages = dle_cache("iChat_window", $config['skin'], true);

include ENGINE_DIR . '/modules/iChat/build.php';

$_SESSION['hash_messages_' . $_POST['place']] = md5($Messages);

@header("Content-type: text/html; charset=" . $config['charset']);

echo $Messages;

if (!$error)
    $js_c = "document.getElementById('iChat_form').message.value = '';";
else
    $js_c = "DLEalert('" . $error . "', '" . $chat_lang['title'] . "')";

echo '<script language="JavaScript" type="text/javascript">' . $js_c . '</script>';

?>