/*
 * page base action
 */
LP.use(['jquery', 'api', 'easing'] , function( $ , api ){
    'use strict'

    // widgets and common functions here
    // ======================================================================
    var rotateAnimate = function( $dom , current , total , easing , startAngle ){
        var percent = current / total;
        startAngle = startAngle / 180 * Math.PI || Math.PI*3/2;
        LP.use('raphaeljs' , function( Raphael ){
            // Creates canvas 320 × 200 at 10, 50
            var width = $dom.width();
            var height = $dom.height();
            var r = 35 , stockWidth = 10 , stockColor = "#ff0707";

            var start = [ width / 2 + Math.cos( startAngle )  * r , height / 2 + Math.sin( startAngle ) * r ];
            var end = [ width / 2 + Math.cos( startAngle + percent * 2 * Math.PI ) * r , height / 2 + Math.sin( startAngle + percent * 2 * Math.PI ) * r  ]
            console.log( start , end );
            var path = [
                'M' , start[0] , ' ' , start[1] ,
                'A' , r , ' ' , r , ' 0 ' , percent > 0.5 ? '1' : '0' , ' 1 ' ,  end[0] , ' ' , end[1]
                ].join("");
            var otherPath = [
                'M' , start[0] , ' ' , start[1] ,
                'A' , r , ' ' , r , ' 0 ' , percent > 0.5 ? '0' : '1' , ' 0 ' ,  end[0] , ' ' , end[1]
            ].join("");

            var paper = Raphael( $dom.get(2) , width , height , function(){
                // Creates circle at x = 50, y = 40, with radius 10
                var circle = this.circle( width / 2 , height / 2, 35 );
                // Sets the fill attribute of the circle to red (#f00)
                // circle.attr("stroke", "#000")
                //     .attr("stroke-width" , 10);
                this.path( otherPath )
                    .attr("stroke", "#000")
                    .attr("stroke-width" , 10);
                this.path( path )
                    .attr("stroke" , stockColor )
                    .attr("stroke-width" ,stockWidth );
            });
        });
    }


    // page actions here
    // ======================================================================
    LP.action("login" ,function(){
        api.ajax( "login" , $(this).closest('form').serialize() , function( ){

        } );
        return false;
    });
    LP.action( "weibo-connect" , function(){
        LP.panel({
            title: 'weibo oauth',
            content: 'hello world'
        });
    } );

    LP.action( "twitter-connect" , function(){
        alert('twitter');
    } );




    // page init here
    // =======================================================================
    $(function(){
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
        });
        // login
        $('.login_tips').live('click',function(){
            $(this).hide()
            $(this).prev('input').focus()
        });
        $('.loginipt input').live('blur',function(){
            if($(this).val() == ''){
                $(this).next('.login_tips').show()
            }                
        });


        if( $('.member_speed').length ){
            rotateAnimate( $('.member_speed') , 100 , 360 );
        }


        // init first page template
        switch( CONFIG.page ){
            case "home":
                api.get("./api/weibo/loginurl" , function( e ){
                    LP.compile( "init-tpl" , {weibo_url: e.data.url , twitter_url: e.data.url} , function( html ){
                        $(document.body).append( html );
                    } )
                });
                break;
        }
    });
});


