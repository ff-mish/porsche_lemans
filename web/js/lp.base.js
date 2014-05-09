/*
 * page base action
 */
LP.use(['jquery', 'api', 'easing'] , function( $ , api ){
    'use strict'

    // widgets and common functions here
    // ======================================================================
    var rotateAnimate = (function(){

        function rotateRun( $dom , current , total , easing , startAngle ){
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
            // raphaeljs
            // $dom.html( ['<div class="rotate-wrap"><div class="display"></div>',
            //             '<div class="front left"></div>',
            //             '<div class="rotate left">',
            //                 '<div class="bg left"></div>',
            //             '</div>',
            //             '<div class="rotate right">',
            //                 '<div class="bg right"></div>',
            //             '</div></div>'].join("") );
            // var $rotateLeft = $dom.find( ".rotate.left" );
            // var $rotateRight = $dom.find( ".rotate.right" );
            // var $display = $dom.find('.display');
            // // Calculating the current angle:
            // var angle = (360/total)*(current+1);
            // var $element;

            // if(current==0){
            //     // Hiding the right half of the background:
            //     $rotateRight.hide();
            //     // Resetting the rotation of the left part:
            //     rotateElement($rotateLeft,0);
            // }
            
            // if(angle<=180){
            //     // The left part is rotated, and the right is currently hidden:
            //     $element = $rotateLeft;
            // } else {
            //     // The first part of the rotation has completed, so we start rotating the right part:
            //     $rotateRight.show();
            //     $rotateLeft.show();
            //     rotateElement($rotateLeft,180);
            //     $element = $rotateRight;
            //     angle = angle-180;
            // }

            // rotateElement( $element,angle);
            
            // Setting the text inside of the display element, inserting a leading zero if needed:
            // clock.display.html(current<10?'0'+current:current);
        }

        function rotateElement($element,angle){
            // Rotating the $element, depending on the browser:
            var rotate = 'rotate('+angle+'deg)';
            
            if($element.css('MozTransform')!=undefined)
                $element.css('MozTransform',rotate);
                
            else if($element.css('WebkitTransform')!=undefined)
                $element.css('WebkitTransform',rotate);
        
            // A version for internet explorer using filters, works but is a bit buggy (no surprise here):
            else if($element.css("filter")!=undefined) {
                var cos = Math.cos(Math.PI * 2 / 360 * angle);
                var sin = Math.sin(Math.PI * 2 / 360 * angle);
                $element.css("filter","progid:DXImageTransform.Microsoft.Matrix(M11="+cos+",M12=-"+sin+",M21="+sin+",M22="+cos+",SizingMethod='auto expand',FilterType='nearest neighbor')");
                $element.css("left",-Math.floor(($element.width()-200)/2));
                $element.css("top",-Math.floor(($element.height()-200)/2));
            }
        }

        return rotateRun;
        // return {
        //     init: init
        // }

    })();

    rotateAnimate( $('.member_speed') , 100 , 360 );


    // page actions here
    // ======================================================================
    LP.action("login" ,function(){
        api.ajax( "login" , $(this).closest('form').serialize() , function( ){

        } );
        return false;
    });
    LP.action( "weibo-connect" , function(){
        alert('weibo');
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
    });
});


