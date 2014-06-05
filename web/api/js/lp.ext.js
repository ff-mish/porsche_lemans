/*
 * page base action
 */

LP.use(['jquery', 'api', 'easing'] , function( $ , api ){
    'use strict'
    
    LP.action("invite_box", function(params) {
      LP.panel({
        type: "panel",
        "content": '<div class="popup_box popup_dialog"><div class="popup_dialog_msg">Do you want to join '+params["team_name"]+' ?</div><div class="popup_dialog_btns"><a href="javascript:void(0);" class="cancel">Cancel</a><a href="javascript:void(0);" class="confirm">Confirm</a></div></div>',
        "title": "",
        mask: true,
        destroy: true,
        submitButton: false,
        cancelButton: false,
        closeAble: false,
        onShow: function () {
          var panel = this;
          this.$panel.find(".cancel").click(function () {
            api.post("/api/user/jointeam", {"owner": 0}, function(e) {
              if (e["status"] == 0) {
                panel.close();
                window.location.reload();
              }
            });
            panel.close();
          });
          this.$panel.find(".confirm").click(function () {
            api.post("/api/user/jointeam", {"team_id": params["team_id"]}, function(e) {
              if (e["status"] == 0) {
                panel.close();
                window.location.reload();
              }
            });
          });
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
                  LP.triggerAction('invite_box', {"team_name": dataCon.attr("data-team_name"), "team_id": dataCon.attr("data-team_id")});
                }
                break;
        }
    });
});


