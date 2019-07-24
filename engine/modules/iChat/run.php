<?php
if (file_exists(ENGINE_DIR . '/classes/plugins.class.php')) {
    require_once ENGINE_DIR . '/classes/plugins.class.php';
}
if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

if ($config['version_id'] < '10.5')
    die(" iChat 6.1 is not supported by your version of DLE! To work needed DLE 10.5 and higher!");

include ENGINE_DIR . '/modules/iChat/data/config.php';
include_once ENGINE_DIR . '/modules/iChat/data/language.lng';

if (!$chat_cfg['chat_status'] AND $member_id['user_group'] != 1) {
    echo $chat_lang['chat_status'];
} else {
    
    $is_change = false;
    
    if (!$config['allow_cache']) {
        $config['allow_cache'] = 1;
        $is_change             = true;
    }
    
    if ($_POST['place'] != 'window')
        $_POST['place'] = 'site';
    
    if ($_POST['place'] == 'site')
        $Messages = dle_cache("iChat", $config['skin'], true);
    if ($_POST['place'] == 'window')
        $Messages = dle_cache("iChat_window", $config['skin'], true);
    
    include ENGINE_DIR . '/modules/iChat/build.php';
    
    $_SESSION['hash_messages_' . $_POST['place']] = md5($Messages);
    
    echo '<form  method="post" name="iChat_form" id="iChat_form" action="/">   

<link rel="stylesheet" type="text/css" href="' . $config['http_home_url'] . 'templates/' . $config['skin'] . '/iChat/css/style.css" />';
    
    echo "\n<script type=\"text/javascript\" src=\"{$config['http_home_url']}templates/{$config['skin']}/iChat/js/action.js\"></script>\n";
    
    echo <<<HTML
<script type="text/javascript">
<!--
var iChat_cfg = ["{$chat_cfg['max_text']}", "{$chat_cfg['refresh']}"];

var iChat_lang = ["{$chat_lang['edit_msg']}", "{$chat_lang['new_text_msg']}", "{$chat_lang['title']}", "{$chat_lang['null']}", "{$chat_lang['max']}", "{$chat_lang['rules_accept']}", "{$chat_lang['save']}", "{$chat_lang['clear']}", "{$chat_lang['updates']}"];

function reFreshiChat()
{
iChatRefresh('{$_POST['place']}');
return false;
};

setInterval(reFreshiChat , iChat_cfg[1]*1000);
//-->
</script>
HTML;
    
    if ($_POST['place'] != 'window')
        $tpl->load_template('skin.tpl');
    else
        $tpl->load_template('window_skin.tpl');
    
    if ($is_logged AND in_array($member_id['user_group'], $allow_group = explode(',', $chat_cfg['group_msg']))) {
        $name = (isset($_COOKIE['iChat_name'])) ? $_COOKIE['iChat_name'] : $chat_lang['name'];
        $mail = (isset($_COOKIE['iChat_mail'])) ? $_COOKIE['iChat_mail'] : $chat_lang['mail'];
        $tpl->set('{name}', $name);
        $tpl->set('{mail}', $mail);
        $tpl->set('{def_name}', $chat_lang['name']);
        $tpl->set('{def_mail}', $chat_lang['mail']);
    }
    
    if (in_array($member_id['user_group'], $allow_group = explode(',', $chat_cfg['history']))) {
        
        $tpl->set('[history]', "");
        $tpl->set('[/history]', "");
        
    } else {
        
        $tpl->set_block("'\\[history\\](.*?)\\[/history\\]'si", "");
    }
    
    $tpl->set('{messages}', $Messages);
    $tpl->set('{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'] . '/iChat');
    
    $tpl->compile('skin');
    
    if ($user_group[$member_id['user_group']]['allow_url'])
        $tpl->result['skin'] = preg_replace("'\[allow_url\](.*?)\[/allow_url\]'si", "\\1", $tpl->result['skin']);
    else
        $tpl->result['skin'] = preg_replace("'\[allow_url\](.*?)\[/allow_url\]'si", "", $tpl->result['skin']);
    
    if (!in_array($member_id['user_group'], $allow_group = explode(',', $chat_cfg['group_msg'])))
        $tpl->result['skin'] = preg_replace("'\[no_access\](.*?)\[/no_access\]'si", "\\1", $tpl->result['skin']);
    else
        $tpl->result['skin'] = preg_replace("'\[no_access\](.*?)\[/no_access\]'si", "", $tpl->result['skin']);
    
    if (in_array($member_id['user_group'], $allow_group = explode(',', $chat_cfg['group_msg'])))
        $tpl->result['skin'] = preg_replace("'\[editor_form\](.*?)\[/editor_form\]'si", "\\1", $tpl->result['skin']);
    else
        $tpl->result['skin'] = preg_replace("'\[editor_form\](.*?)\[/editor_form\]'si", "", $tpl->result['skin']);
    
    echo $tpl->result['skin'];
    
    $tpl->global_clear();
    
    if (in_array($member_id['user_group'], $allow_group = explode(',', $chat_cfg['group_msg']))) {
        $i      = 0;
        $output = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"><tr>";
        
        $smilies       = explode(",", $chat_cfg['smiles']);
        $count_smilies = count($smilies);
        
        foreach ($smilies as $smile) {
            $i++;
            $smile = trim($smile);
            
            $output .= "<td style=\"padding:2px;\" align=\"center\"><a href=\"#\" onclick=\"iChat_smiley(':{$smile}:'); return false;\"><img style=\"border: none;\" alt=\"{$smile}\" src=\"" . $config['http_home_url'] . "engine/modules/iChat/emoticons/{$smile}.gif\" /></a></td>";
            
            if ($i % 4 == 0 AND $i < $count_smilies)
                $output .= "</tr><tr>";
            
        }
        
        $output .= "</tr></table>";
        
        echo '<div id="iChat_emos" style="display: none;" title="' . $lang['bb_t_emo'] . '"><div style="width:100%;height:100%;overflow: auto;">' . $output . '</div></div>';
    }
    
    echo "</form>";
    
    if ($is_change)
        $config['allow_cache'] = false;
}
?>