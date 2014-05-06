/**
 *
 * PORSCHE
 * zx 2014 05 04
 *
 */
(function ($) {
    var Index={
        /**
         * 初始化方法,用于功能函数的入口
         * @param {string}
         * @example
         **/
        init:function(){
            //事件绑定
            this.bindEvent();
        },
        /**
         * 事件绑定
         * @param {string}
         * @example
         **/
        bindEvent:function(){
            //fuel
            $('.fuelitem').live({
                'mouseenter':function(){
                    $(this).children('.fuelshade').show()
                    $(this).children('.fuelbtnbox').show()
                },
                'mouseleave':function(){
                    $(this).children('.fuelshade').hide()
                    $(this).children('.fuelbtnbox').hide()
                }
            })
            // login
            $('.login_tips').live('click',function(){
                $(this).hide()
                $(this).prev('input').focus()
            })
            $('.loginipt input').live('blur',function(){
                if($(this).val() == ''){
                    $(this).next('.login_tips').show()
                }                
            })
        }
    };
    Index.init();  
})(jQuery);




