[group=1]
<input class="bbcodes" style="font-size: 11px; float: right;" title="Ustawienia Czatu" onclick="iChatAdmin(); return false;" type="button" value="Ustawienia Czatu" />
<br />&nbsp;
[/group]
<div id="iChat-style" style="width:max;height:385px; overflow:auto;">
   <div id="iChat-messages" align="left">{messages}</div>
</div>
<br />
[editor_form]
<div class="iChat_editor">
   <div class="iChat_bbeditor">
      <span id="b_b" onclick="iChat_simpletag('b')"><img title="Pogrubienie" src="{THEME}/img/bbcode/b.png" alt="" /></span>
      <span id="b_i" onclick="iChat_simpletag('i')"><img title="Kursywa" src="{THEME}/img/bbcode/i.png" alt="" /></span>
      <span id="b_u" onclick="iChat_simpletag('u')"><img title="Podkreślony tekst" src="{THEME}/img/bbcode/u.png" alt="" /></span>
      <span id="b_s" onclick="iChat_simpletag('s')"><img title="Przekreślony tekst" src="{THEME}/img/bbcode/s.png" alt="" /></span>
      <img class="bbspacer" src="{THEME}/img/bbcode/brkspace.png" alt="" />
      <span id="b_emo" onclick="iChat_ins_emo(this);"><img title="Wstawianie emotikonów" src="{THEME}/img/bbcode/emo.png" alt="" /></span>
      [allow_url]
      <span id="b_quote" onclick="iChat_tag_leech()"><img title="Wstaw bezpieczny link" src="{THEME}/img/bbcode/link.png" alt="" /></span>
      [/allow_url]
      <span id="b_color" onclick="iChat_ins_color(this);"><img title="Kolor tekstu" src="{THEME}/img/bbcode/color.png" alt="" /></span>
      <!-- <span id="b_hide" onclick="iChat_simpletag('hide')"><img title="Ukryty tekst" src="{THEME}/img/bbcode/hide.png" alt="" /></span> -->
      <span id="b_quote" onclick="iChat_simpletag('quote')"><img title="Wstawianie cytatu" src="{THEME}/img/bbcode/quote.png" alt="" /></span>
	  <span onclick="iChat_simpletag('code')"><img title="Wstawianie kodu" src="{THEME}/img/bbcode/code.png" alt="" /></span>
      <span id="b_translit" onclick="iChat_translit()"><img title="Konwertuj zaznaczony tekst z transliteracji na alfabet cyrylicy" src="{THEME}/img/bbcode/translit.png" alt="" /></span>
      <div class="clr"></div>
   </div>
   <textarea name="message" id="message"></textarea>
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
   <input class="bbcodes" style="font-size: 11px; float: right;" title="Wyślij" onclick="iChatAdd('window'); return false;" type="button" value="Wyślij" />
</div>
[/editor_form]
[no_access]
<div class="ui-state-error ui-corner-all" style="padding:9px;text-align:center;">
Chat tylko dla zarejestrowanych. <a href="/index.php?do=register"><font color="red"><b>Zarejestruj się!</b></font><div class="clr"></div>
</div>
[/no_access]