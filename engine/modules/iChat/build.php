<?php
if (file_exists(ENGINE_DIR . '/classes/plugins.class.php')) {
    require_once ENGINE_DIR . '/classes/plugins.class.php';
}

if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

require_once ENGINE_DIR . '/classes/templates.class.php';

$tpl      = new dle_template();
$tpl->dir = ROOT_DIR . '/templates/' . $config['skin'] . '/iChat';
define('TEMPLATE_DIR', $tpl->dir);

if ($Messages === false) {
    
    $_POST['page'] = ($_POST['page'] >= 1) ? $_POST['page'] : 1;
    
    if ($_POST['place'] == 'history')
        $limit = ($_POST['page'] * $chat_cfg['sum_msg_history']) - $chat_cfg['sum_msg_history'] . ',' . ($chat_cfg['sum_msg_history'] + 1);
    
    if ($_POST['place'] == 'site' OR $_POST['place'] == 'window')
        $limit = '0,' . $chat_cfg['sum_msg'];
    
    $db->query("SELECT i.*, u.lastdate, u.name FROM " . PREFIX . "_iChat i LEFT JOIN " . USERPREFIX . "_users u ON i.author=u.name ORDER BY date DESC LIMIT {$limit}");
    
    switch ($_POST['place']) {
        
        case "site":
            $tpl->load_template('message.tpl');
            break;
        
        case "window":
            $tpl->load_template('window_message.tpl');
            break;
        
        case "history":
            $tpl->load_template('history_message.tpl');
            break;
            
    }
    
    $i = 1;
    
    while ($row = $db->get_row()) {
        
        if ($row['user_group'] == '5') {
            
            $author = $user_group[$row['user_group']]['group_prefix'] . $row['author'] . $user_group[$row['user_group']]['group_suffix'];
            
        } else {
            
            if ($config['allow_alt_url'])
                $go_page = $config['http_home_url'] . "user/" . urlencode($row['author']) . "/";
            else
                $go_page = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode($row['author']);
            
            $author = "<a onclick=\"ShowProfile('" . urlencode($row['author']) . "', '" . $go_page . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\" href=\"" . $go_page . "\">" . $user_group[$row['user_group']]['group_prefix'] . $row['author'] . $user_group[$row['user_group']]['group_suffix'] . "</a>";
            
        }
        
        if (count(explode("@", $row['foto'])) == 2) {
            $tpl->set('{gravatar}', $row['foto']);
            
            $tpl->set('{foto}', '//www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']));
            
        } else {
            
            if ($row['foto']) {
                
                if (strpos($row['foto'], "//") === 0)
                    $avatar = "http:" . $row['foto'];
                else
                    $avatar = $row['foto'];
                
                $avatar = @parse_url($avatar);
                
                if ($avatar['host']) {
                    
                    $tpl->set('{foto}', $row['foto']);
                    
                } else
                    $tpl->set('{foto}', $config['http_home_url'] . "uploads/fotos/" . $row['foto']);
                
            } else
                $tpl->set('{foto}', $config['http_home_url'] . 'templates/' . $config['skin'] . "/dleimages/noavatar.png");
            
            $tpl->set('{gravatar}', '');
        }
        if ($row['author'] == $row['name']) {
            if (($row['lastdate'] + 1200) > $_TIME) {
                
                $tpl->set('[online]', "");
                $tpl->set('[/online]', "");
                $tpl->set_block("'\\[offline\\](.*?)\\[/offline\\]'si", "");
                
            } else {
                $tpl->set('[offline]', "");
                $tpl->set('[/offline]', "");
                $tpl->set_block("'\\[online\\](.*?)\\[/online\\]'si", "");
            }
            
            
        } else {
            
            $tpl->set_block("'\\[online\\](.*?)\\[/online\\]'si", "");
            $tpl->set_block("'\\[offline\\](.*?)\\[/offline\\]'si", "");
        }
        
        $tpl->set('{id}', $row['id']);
        if (@date("Ymd", $row['date']) == date("Ymd", $_TIME)) {
            
            $tpl->set('{date}', $lang['time_heute'] . langdate(", H:i", $row['date']));
            
        } elseif (@date("Ymd", $row['date']) == date("Ymd", ($_TIME - 86400))) {
            
            $tpl->set('{date}', $lang['time_gestern'] . langdate(", H:i", $row['date']));
            
        } else {
            
            $tpl->set('{date}', langdate($chat_cfg['format_date'], $row['date']));
            
        }
        $tpl->set('{author}', stripslashes($author));
        $tpl->set('{name}', stripslashes($row['author']));
        $tpl->set('{message}', $row['message']);
        $tpl->set('{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'] . "/iChat");
        
        if ($_POST['place'] == 'site' OR $_POST['place'] == 'window' OR $_POST['place'] == 'history' AND $i <= $chat_cfg['sum_msg_history'])
            $tpl->compile('message');
        
        $i++;
        
    }
    
    $db->free();
    $tpl->clear();
    
    $Messages = $tpl->result['message'];
    
    if (!$Messages)
        $Messages = $chat_lang['no_messages'];
    
    if ($_POST['place'] == 'history') {
        
        $new_record      = '<center><input class="bbcodes" style="font-size: 11px;" title="' . $chat_lang['new_record'] . '" onclick="iChatHistory(' . ($_POST['page'] - 1) . '); return false;" type="button" value="' . $chat_lang['new_record'] . '" /></center><br/>';
        $previous_record = '<br/><center><input class="bbcodes" style="font-size: 11px;" title="' . $chat_lang['previous_record'] . '" onclick="iChatHistory(' . ($_POST['page'] + 1) . '); return false;" type="button" value="' . $chat_lang['previous_record'] . '" /></center>';
        
        if ($_POST['page'] > 1)
            $Messages = $new_record . $Messages;
        if ($i > $chat_cfg['sum_msg_history'])
            $Messages = $Messages . $previous_record;
        
    }
    
    switch ($_POST['place']) {
        
        case "site":
            create_cache("iChat", $Messages, $config['skin'], true);
            break;
        
        case "window":
            create_cache("iChat_window", $Messages, $config['skin'], true);
            break;
        
        case "history":
            create_cache("iChat_history_" . $_POST['page'], $Messages, $config['skin'], true);
            break;
            
    }
    
}

if ($_POST['place'] == 'history')
    $_SESSION['page'] = $_POST['page'];

if (in_array($member_id['user_group'], $allow_group = explode(',', $chat_cfg['group_msg'])))
    $Messages = preg_replace("'\[allow_reply\](.*?)\[/allow_reply\]'si", "\\1", $Messages);
else
    $Messages = preg_replace("'\[allow_reply\](.*?)\[/allow_reply\]'si", "", $Messages);

if ($user_group[$member_id['user_group']]['edit_allc'])
    $Messages = preg_replace("'\[allow_edit\](.*?)\[/allow_edit\]'si", "\\1", $Messages);
else
    $Messages = preg_replace("'\[allow_edit\](.*?)\[/allow_edit\]'si", "", $Messages);

if ($user_group[$member_id['user_group']]['del_allc'])
    $Messages = preg_replace("'\[allow_delete\](.*?)\[/allow_delete\]'si", "\\1", $Messages);
else
    $Messages = preg_replace("'\[allow_delete\](.*?)\[/allow_delete\]'si", "", $Messages);

if ($user_group[$member_id['user_group']]['allow_hide'])
    $Messages = preg_replace("'\[hide\](.*?)\[/hide\]'si", "\\1", $Messages);
else
    $Messages = preg_replace("'\[hide\](.*?)\[/hide\]'si", "<div class=\"quote\">" . $chat_lang['hide'] . "</div>", $Messages);

if ($member_id['user_group'] == 1)
    $Messages = preg_replace("'\[admin\](.*?)\[/admin\]'si", "\\1", $Messages);
else
    $Messages = preg_replace("'\[admin\](.*?)\[/admin\]'si", "<div class=\"quote\">" . $chat_lang['admin_hide'] . "</div>", $Messages);

?>