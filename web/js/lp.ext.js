/*
 * page base action
 */

LP.use(['jquery', 'api', 'easing'] , function( $ , api ){
    'use strict'
    
    LP.action("invite_box", function() {
      console.log(LP.panel);
      LP.panel({
        type: "panel",
        "content": '<div class="popup_box popup_dialog"><div class="popup_dialog_msg">Do you want to join {{team_name}} ?</div><div class="popup_dialog_btns"><a href="javascript:void(0);">Cancel</a><a href="javascript:void(0);">Confirm</a></div></div>',
        "title": "",
        mask: true,
        destroy: true,
        submitButton: false,
        cancelButton: false,
        closeAble: false,
        onShow: function () {
          //TODO::
        },
        onSubmit: function () {
          
        },
        onCancel: function () {

        },
        width: $(window).width() * 0.6,
      });
    });
    
    $(function () {
        // init first page template
        switch( $(document.body).data('page') ){
            
                
              case "stand":
                var dataCon = $("#data-stand");
                var isInvited = dataCon.attr("data-is_invited");
                if (parseInt(isInvited) != 0) {
                  LP.triggerAction('invite_box');
                }
                break;
        }
    });
});


