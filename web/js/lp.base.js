/*
 * page base action
 */
LP.use(['jquery', 'api', 'easing'] , function( $ , api ){
    'use strict'

    // widgets and common functions here
    // ======================================================================
    var rotateAnimate = (function(){

        function rotateRun( $dom , current , total , easing ){
            $dom.html( ['<div class="rotate-wrap"><div class="display"></div>',
                        '<div class="front left"></div>',
                        '<div class="rotate left">',
                            '<div class="bg left"></div>',
                        '</div>',
                        '<div class="rotate right">',
                            '<div class="bg right"></div>',
                        '</div></div>'].join("") );
            var $rotateLeft = $dom.find( ".rotate.left" );
            var $rotateRight = $dom.find( ".rotate.right" );
            var $display = $dom.find('.display');
            // Calculating the current angle:
            var angle = (360/total)*(current+1);
            var $element;

            if(current==0){
                // Hiding the right half of the background:
                $rotateRight.hide();
                // Resetting the rotation of the left part:
                rotateElement($rotateLeft,0);
            }
            
            if(angle<=180){
                // The left part is rotated, and the right is currently hidden:
                $element = $rotateLeft;
            } else {
                // The first part of the rotation has completed, so we start rotating the right part:
                $rotateRight.show();
                $rotateLeft.show();
                rotateElement($rotateLeft,180);
                $element = $rotateRight;
                angle = angle-180;
            }

            rotateElement( $element,angle);
            
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


