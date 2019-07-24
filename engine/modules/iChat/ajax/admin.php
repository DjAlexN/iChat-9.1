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

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set($config['date_adjust']);

include_once ENGINE_DIR . '/modules/iChat/data/language.lng';

if ($config['http_home_url'] == "") {
    
    $config['http_home_url'] = explode("engine/modules/iChat/ajax/admin.php", $_SERVER['PHP_SELF']);
    $config['http_home_url'] = reset($config['http_home_url']);
    $config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
    
}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

require_once ENGINE_DIR . '/modules/sitelogin.php';

if (($member_id['user_group'] != 1)) {
    die("error");
}

if ($config["lang_" . $config['skin']]) {
    
    if (file_exists(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/adminpanel.lng')) {
        @include_once(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/adminpanel.lng');
    } else
        die("Language file not found");
    
} else {
    
    include_once ROOT_DIR . '/language/' . $config['langs'] . '/adminpanel.lng';
    
}

@header("Content-type: text/html; charset=" . $config['charset']);

include ENGINE_DIR . '/modules/iChat/data/config.php';

if ($_POST['action'] == "save") {
    
    $save_cfg = $_POST['save_cfg'];
    
    $save_cfg['version'] = "8.0";
    
    if (!is_numeric(trim($save_cfg['sum_msg'])) OR trim($save_cfg['sum_msg']) <= 0) {
        $save_cfg['sum_msg'] = 10;
    }
    if (!is_numeric(trim($save_cfg['sum_msg_history'])) OR trim($save_cfg['sum_msg_history']) <= 0) {
        $save_cfg['sum_msg_history'] = 25;
    }
    if (!is_numeric(trim($save_cfg['max_text'])) OR trim($save_cfg['max_text']) <= 0) {
        $save_cfg['max_text'] = 300;
    }
    if (!is_numeric(trim($save_cfg['refresh'])) OR trim($save_cfg['refresh']) <= 0) {
        $save_cfg['refresh'] = 15;
    }
    if (!is_numeric(trim($save_cfg['max_word'])) OR trim($save_cfg['max_word']) <= 0) {
        $save_cfg['max_word'] = 33;
    }
    if (!is_numeric(trim($save_cfg['cron_clean'])) OR trim($save_cfg['cron_clean']) <= 0) {
        $save_cfg['cron_clean'] = 15;
    }
    if (!is_numeric(trim($save_cfg['stop_flood'])) OR trim($save_cfg['stop_flood']) < 0) {
        $save_cfg['stop_flood'] = 30;
    }
    
    $save_cfg['no_access']       = htmlspecialchars($save_cfg['no_access'], ENT_QUOTES, $config['charset']);
    $save_cfg['guest_name']      = htmlspecialchars($save_cfg['guest_name'], ENT_QUOTES, $config['charset']);
    $save_cfg['word_filter_arr'] = htmlspecialchars($save_cfg['word_filter_arr'], ENT_QUOTES, $config['charset']);
    
    $save_cfg = $save_cfg + $chat_cfg;
    
    $handler = fopen(ENGINE_DIR . '/modules/iChat/data/config.php', "w");
    
    fwrite($handler, "<?PHP \n\n//iChat Configurations\n\n\$chat_cfg = array (\n\n");
    foreach ($save_cfg as $name => $value) {
        
        $value = str_replace("%3D", "=", $value);
        
        $value = str_replace("$", "&#036;", $value);
        $value = str_replace("{", "&#123;", $value);
        $value = str_replace("}", "&#125;", $value);
        
        $name = str_replace("$", "&#036;", $name);
        $name = str_replace("{", "&#123;", $name);
        $name = str_replace("}", "&#125;", $name);
        
        fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
        
    }
    fwrite($handler, ");\n\n?>");
    fclose($handler);
    
    include ENGINE_DIR . '/modules/iChat/data/config.php';
    
}

if (!$chat_cfg['chat_status']) {
    $status = $chat_lang['admin20'];
} else {
    $status = "";
}

$js_data = @file_get_contents(ROOT_DIR . '/templates/' . $config['skin'] . '/iChat/js/admin.js');

function makeDropDown($options, $name, $selected)
{
    $output = "<select id=\"{$name}\" name=\"{$name}\">\r\n";
    foreach ($options as $value => $description) {
        $output .= "<option value=\"{$value}\"";
        if ($selected == $value) {
            $output .= " selected ";
        }
        $output .= ">{$description}</option>\n";
    }
    $output .= "</select>";
    return $output;
}

$allow_sound = makeDropDown(array(
    "1" => $lang['opt_sys_yes'],
    "0" => $lang['opt_sys_no']
), "cfg17", "{$chat_cfg['allow_sound']}");

$guest_name_suffix = makeDropDown(array(
    "1" => $lang['opt_sys_yes'],
    "0" => $lang['opt_sys_no']
), "cfg20", "{$chat_cfg['guest_name_suffix']}");

$word_filter_status = makeDropDown(array(
    "1" => $lang['opt_sys_yes'],
    "0" => $lang['opt_sys_no']
), "cfg22", "{$chat_cfg['word_filter_status']}");

$chat_status = makeDropDown(array(
    "1" => $lang['opt_sys_yes'],
    "0" => $lang['opt_sys_no']
), "cfg08", "{$chat_cfg['chat_status']}");

$row   = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_iChat");
$count = $row['count'];

$content = <<<HTML
<script language="javascript" type="text/javascript">
var iChat_lang_loading;
{$js_data}
</script>
<div id="contents">

<b>{$chat_lang['admin13']}</b> {$chat_status}
     <hr />
<b>{$chat_lang['admin1']}</b> 
<input id="cfg14" type=text style="text-align: center;"  value="{$chat_cfg['group_msg']}" size=10><br />
     <hr />
<b>{$chat_lang['admin14']}</b> {$allow_sound}
     <hr />
<b>{$chat_lang['admin2']}</b>
<input id="cfg01" type=text style="text-align: center;"  value="{$chat_cfg['sum_msg']}" size=10><br />
    <hr />
<b>{$chat_lang['admin15']}</b>
<input id="cfg18" type=text style="text-align: center;"  value="{$chat_cfg['guest_name']}" size=10><br />
     <hr />
<b>{$chat_lang['admin17']}</b> {$guest_name_suffix}
     <hr />
<b>{$chat_lang['admin18']}</b>
<input id="cfg21" type=text style="text-align: center;"  value="{$chat_cfg['history']}" size=10><br />
     <hr />
<b>{$chat_lang['admin3']}</b>
<input id="cfg15" type=text style="text-align: center;"  value="{$chat_cfg['sum_msg_history']}" size=10><br />
     <hr />
<b>{$chat_lang['admin4']}</b>
<input id="cfg12" type=text style="text-align: center;"  value="{$chat_cfg['max_text']}" size=10><br />
     <hr />
<b>{$chat_lang['admin5']}</b>
<input id="cfg13" type=text style="text-align: center;"  value="{$chat_cfg['format_date']}" size=10><br />
     <hr />
<b>{$chat_lang['admin6']}</b>
<input id="cfg02" type=text style="text-align: center;" value="{$chat_cfg['refresh']}" size=10><br />
     <hr />
<b>{$chat_lang['admin7']}</b>
<input id="cfg03" type=text style="text-align: center;"  value="{$chat_cfg['stop_flood']}" size=10><br />
     <hr />
<b>{$chat_lang['admin8']}</b>
<input id="cfg04" type=text style="text-align: center;"  value="{$chat_cfg['max_word']}" size=10><br />
     <hr />
<b>{$chat_lang['admin9']}</b>
<input id="cfg05" type=text style="text-align: center;"  value="{$chat_cfg['cron_clean']}" size=10><br />
     <hr />
<b>{$chat_lang['admin10']}</b>
<input id="cfg16" type=text style="text-align: center;"  value="{$chat_cfg['no_access']}" size=28><br />
	 <hr />
<b>{$chat_lang['admin19']}</b> {$word_filter_status}
     <hr />
<b>{$chat_lang['admin16']}</b>
<input id="cfg19" type=text style="text-align: center;"  value="{$chat_cfg['word_filter_arr']}" size=45><br />
     <hr />
<b>{$chat_lang['admin11']}</b>
<input id="cfg07" type=text style="text-align: center;"  value="{$chat_cfg['stop_bbcode']}" size=28><br />
     <hr />
<b>{$chat_lang['admin12']}</b>
<input id="cfg06" type=text style="text-align: center;"  value="{$chat_cfg['smiles']}" size=55><br />
     <hr />
- W bazie danych <b style="color:red">{$count}</b> wiadomości.
<div id="progres"></div>

</div>
HTML;

if ($_POST['check'] == "updates") {
    
    $data = "<h2 style=\"color:red\">Ta adaptacja nie ma zadnych aktualizacji!<br /> Wszelkie pytania mozna zadac <a href=\"https://dj-alexn.pl/forum\" target=\"_blank\">Tutaj</a> lub <a href=\"mailto:djalexn.graphic@gmail.com?subject=Pomoc przy iChat {$chat_cfg['version']}\">Tutaj</a></h2>";
	
     echo $data;
   
    die();
}

if ($_POST['action'] == "save" OR $_POST['action'] == "clear") {
    
    if ($_POST['action'] == "clear") {
        $db->query("TRUNCATE TABLE " . PREFIX . "_iChat");
    }
    
    echo $content;
    clear_cache('iChat_');
}

echo "<div id='ECPU' title='{$chat_lang['admin_title']} {$status}' style='display:none'>{$content}</div>";

?>