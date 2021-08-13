jQuery(function($){
    $('div.flagallery noscript').remove();
});

function waitJQv(fvVar, fvW, fvH){
    if(typeof(jQuery) === 'undefined'){
        window.setTimeout(waitJQ, 100);
    } else{
        if(typeof(jQuery.fn.fancybox) === 'undefined'){
            jQuery("head").append("<script type='text/javascript' src='" + fvVar + "assets/fancybox/jquery.fancybox-1.3.4.pack.js'></script><link rel='stylesheet' href='" + fvVar + "assets/fancybox/jquery.fancybox-1.3.4.css' type='text/css' media='screen' />");
        }
        waitFBv(fvW, fvH);
    }
}

function waitFBv(fvW, fvH){
    if(typeof(jQuery.fn.fancybox) === 'undefined'){
        window.setTimeout(waitFB, 100);
    } else{
        jQuery(function($){
            $('.flag_fancyvid').fancybox({
                'type': 'iframe',
                'width': fvW,
                'height': fvH,
                'overlayShow': true,
                'overlayOpacity': '0.5',
                'showNavArrows': false
            });
        });
    }
}