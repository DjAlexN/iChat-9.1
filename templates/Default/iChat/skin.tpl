[group=1]
<input class="bbcodes" style="font-size: 11px; float: right;" title="Ustawienia Czata" onclick="iChatAdmin(); return false;" type="button" value="Ustawienia Czata" />
<br />&nbsp;
[/group]
<div id="iChat-style" style="width:max;height:300px; overflow:auto;">
   <div id="iChat-messages" align="left">{messages}</div>
</div>
<br />
[editor_form]
<div class="iChat_editor">
   <div class="iChat_bbeditor">
      <span onclick="iChat_simpletag('b')"><img title="Pogrubienie" src="{THEME}/img/bbcode/b.png" alt="" /></span>
      <span onclick="iChat_simpletag('i')"><img title="Pochylony tekst" src="{THEME}/img/bbcode/i.png" alt="" /></span>
      <span onclick="iChat_simpletag('u')"><img title="Podkreślony tekst" src="{THEME}/img/bbcode/u.png" alt="" /></span>
      <span onclick="iChat_simpletag('s')"><img title="Przekreślony tekst" src="{THEME}/img/bbcode/s.png" alt="" /></span>
      <img class="bbspacer" src="{THEME}/img/bbcode/brkspace.png" alt="" />
      <span onclick="iChat_ins_emo(this);"><img title="Wstawianie emotikonów" src="{THEME}/img/bbcode/emo.png" alt="" /></span>
      [allow_url]
      <span onclick="iChat_tag_leech()"><img title="Wstaw bezpieczny link" src="{THEME}/img/bbcode/link.png" alt="" /></span>
      [/allow_url]
      <span onclick="iChat_ins_color(this);"><img title="Kolor tekstu" src="{THEME}/img/bbcode/color.png" alt="" /></span>
      <span onclick="iChat_simpletag('quote')"><img title="Wstawianie cytatu" src="{THEME}/img/bbcode/quote.png" alt="" /></span>
      <span onclick="iChat_simpletag('code')"><img title="Wstawianie kodu" src="{THEME}/img/bbcode/code.png" alt="" /></span>
      <span onclick="iChat_translit()"><img title="Konwertuj zaznaczony tekst z transliteracji na alfabet cyrylicy" src="{THEME}/img/bbcode/translit.png" alt="" /></span>
      <div class="clr"></div>
   </div>
   <textarea name="message" id="message" rows="" cols=""></textarea>
   <div class="clr"></div>
</div>
<script>
document.onkeyup = function (e) {
        e = e || window.event;
        if (e.keyCode === 13) {
          iChatAdd('site'); 
        }
        return false;
    }
    </script>
<div style="padding-top:12px;">
   <input class="bbcodes" style="font-size: 11px; float: left;" title="Regulamin" onclick="iChatRules(); return false;" type="button" value="Regulamin" />&nbsp;
   [history]<input class="bbcodes" style="font-size: 11px; float: left;" title="Archiwum" onclick="iChatHistory(); return false;" type="button" value="Archiwum" />&nbsp;[/history]
   <input class="bbcodes" style="font-size: 11px; float: right;" title="Wyślij" onclick="iChatAdd('site'); return false;" type="button" value="Wyślij" />
</div>
[/editor_form]
[no_access]
<div class="ui-state-error ui-corner-all" style="padding:9px;text-align:center;">
    Chat tylko dla zarejestrowanych. <a href="/index.php?do=register"><font color="red"><b>Zarejestruj się!</b></font><div class="clr"></div>
</div>
[/no_access]