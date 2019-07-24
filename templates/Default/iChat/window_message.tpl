<div class="chat">
   <img alt="" title="" src="{foto}" width="50" border="0" style="padding-bottom:3px"/>
   <div style="float:right;font-size:11px;vertical-align:top">
      [online]
      <div style="color: #64B327;float:right;">Aktywny</div>
      [/online]
      [offline]
      <div style="color: #D53B3B;float:right;">Nieaktywny</div>
      [/offline]
      <br />
	  {date}  
      <br />
      <div align="right">{author}</div>
   </div>
   <div class="hr1"></div>
   <span style="color:#2a2a2a">
      {message}<br />
      <div align="right">
         [allow_reply]<img class="edit_buttons" onmouseover="iChat_copy_quote('{name}');" onclick="iChat_reply('{name}'); return false;" src="{THEME}/img/quote.png" alt="Odpisz" />[/allow_reply]
         [allow_edit]<img class="edit_buttons" onclick="iChatEdit('{id}','window'); return false;" src="{THEME}/img/edit.png" alt="Edytuj"/>[/allow_edit]
         [allow_delete]<img class="edit_buttons" onclick="iChatDelete('{id}','window'); return false;" src="{THEME}/img/delete.png" alt="Usuń"/>[/allow_delete]
      </div>
   </span>
</div>