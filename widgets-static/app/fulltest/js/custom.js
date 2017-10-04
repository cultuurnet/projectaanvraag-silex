/* ========================================================================= */
/*	Preloader
/* ========================================================================= */

jQuery(window).load(function(){



});


$(document).ready(function(){

    /* ========================================================================= */
    /*	Load HTML template
     /* ========================================================================= */

    let ol = false,
        isExternalCssLoaded = false;


    $('body').on('click', '.btn--toggleOutline',  function() {

            let $el = $('.cnw_center, .cnw_sideCol, .cnw_header');

            if(ol === true ){
                $el.css('border', '0px solid gray');
                ol = false;
                $(this).removeClass('btn-success');
            } else {
                $el.css('border', '1px solid gray');
                ol = true;
                $(this).addClass('btn-success');
            }
    });



    $('body').on('click', '.btn--toggleCss',  function() {

            if(isExternalCssLoaded === false ){
                loadExternalCss(true);
                isExternalCssLoaded = true;
                $(this).addClass('btn-success');

            } else {
                loadExternalCss(false);
                isExternalCssLoaded = false;
                $(this).removeClass('btn-success');
            }
    });


    renderCnwComponentTypeSelector = function(componentType, placeHolder, regions) {
        let options;
        let selector = '';
         $.getJSON( 'cnw-templates-config.json').done( function(data) {
            if(componentType === 'layouts') {
                let options = '<option data-templatekey="" value=""> -- select -- </option>';
                    $.each(data[componentType]['elements'], function (key, val) {
                        options += '<option data-templatekey="' + key + '" value="' + val.path + '">' + val.name + '</option>';
                });



                 selector = '<div class="col-sm-8"><select data-path="' +
                    data[componentType]['path'] +
                    '" class="form-control cnw_selector" name="' +
                    componentType + ' " id="#select_' +
                    componentType + '"> ' +
                    options +
                    '</select></div>' +
                     '<div class="col-sm-2"><button class="btn btn--toggleOutline">toggle outline</button></div> ' +
                     '<div class="col-sm-2"><button class="btn btn--toggleCss">remove external css</button></div> ';
                }

             if(componentType === 'partials') {

                 let options = '<option data-templatekey="" value=""> -- select -- </option>';
                // let selector = '';
                 $.each(data[componentType]['elements'], function (key, val) {

                     options += '<option data-templatekey="' + key + '" value="' + val.path + '">' + val.name + '</option>';

                 });


                 $.each(regions, function(regionKey, regionName) {

                     selector += '<div class="col-sm-4"> <label> '+regionName+' <select ' +
                         'data-path="' + data[componentType]['path'] + '" ' +
                         ' data-region="' + regionName + '" ' +
                         'class="form-control cnw_selector" name="' + componentType + ' "' +
                         ' id="#select_' + componentType + '"' +
                         ' id="#select_' + componentType + '"' +
                         '> ' + options +
                         '</select></label></div>';
                 });

             }

             $('#'+placeHolder).html(selector).change( function(e, t) {
                 let    $this = $(this).find('.cnw_selector'),
                        path =  $this.data('path'),
                        template =  $this.val(),
                        templatekey = $this.find(':selected').data('templatekey'),
                        elementId =  $this.attr('id'),
                        parentElementId = $this.find(':selected').parent('select').attr('id');


                         if(componentType === 'layouts') {
                             renderCnwComponentTypeSelector('partials', 'partialsSelector', data.layouts.elements[templatekey].regions);
                             let load = '../'+path+'/'+template + ' #loadcontent';
                             if(elementId === '#select_layouts') {
                                 $('#cnw_w').load(load , function( response, status, xhr ) {
                                     console.log('tempate 1 : ' , status);
                                 });
                             }
                         }

                         if(componentType === 'partials') {

                            template = $(e.originalEvent.target).find(':selected').val()

                             let load = '../'+path+'/'+template + ' #loadcontent';

                                 let regionClass = '.cnw_'+ $(e.originalEvent.target).attr('data-region') ,
                                    $loadIn = $('body').find(regionClass);

                                     $($loadIn).load(load , function( response, status, xhr ) {

                                         if(status === 'success') {

                                             $('body .load').each( function(key, val) {

                                                 $this = $(val);
                                                 path = '../'+$this.data('path').trim();
                                                 $amount = $this.data('amount');

                                                 for(i = 0; i < $amount; i++) {
                                                     $.get(path, function(data){
                                                         $($(data).find('#loadcontent')).appendTo($this)
                                                     }, 'html' );
                                                 }

                                             });
                                         }
                                     });
                         }
                 });

        });

    };

    renderCnwComponentTypeSelector('layouts', 'layoutsSelector');







    /* ========================================================================= */
    /*	load CSS file in HEAD
     /* ========================================================================= */
    let loadExternalCss = function(loaded) {

        let link = document.createElement( 'link' );
        link.href = 'http://widgetbeheer-api.dev/assets/css/main.css';
        link.type = 'text/css';
        link.rel = 'stylesheet';
        link.media = 'screen';
        link.class = 'externalCss';

        if(loaded === false) {
            document.getElementsByTagName( 'head')[0].appendChild( link );
        } else {

            $('link[rel=stylesheet][href="../styles/main.css"]').remove();
        }


    };
    loadExternalCss(isExternalCssLoaded);




    /* ========================================================================= */
	/*	Menu item highlighting
	/* ========================================================================= */

	jQuery('#nav').singlePageNav({
		offset: jQuery('#nav').outerHeight(),
		filter: ':not(.external)',
		speed: 1200,
		currentClass: 'current',
		easing: 'easeInOutExpo',
		updateHash: true,
		beforeStart: function() {
			console.log('begin scrolling');
		},
		onComplete: function() {
			console.log('done scrolling');
		}
	});
	
    $(window).scroll(function () {
        if ($(window).scrollTop() > 400) {
            $("#navigation").css("background-color","#0EB493");
        } else {
            $("#navigation").css("background-color","rgba(16, 22, 54, 0.2)");
        }
    });
	
	/* ========================================================================= */
	/*	Fix Slider Height
	/* ========================================================================= */	

	var slideHeight = $(window).height();
	
	$('#slider, .carousel.slide, .carousel-inner, .carousel-inner .item').css('height',slideHeight);

	$(window).resize(function(){'use strict',
		$('#slider, .carousel.slide, .carousel-inner, .carousel-inner .item').css('height',slideHeight);
	});
	
	
	/* ========================================================================= */
	/*	Portfolio Filtering
	/* ========================================================================= */	
	
	
    // portfolio filtering

    $(".project-wrapper").mixItUp();
	
	
	$(".fancybox").fancybox({
		padding: 0,

		openEffect : 'elastic',
		openSpeed  : 650,

		closeEffect : 'elastic',
		closeSpeed  : 550,

		closeClick : true,
	});
	
	/* ========================================================================= */
	/*	Parallax
	/* ========================================================================= */	
	
	$('#facts').parallax("50%", 0.3);
	
	/* ========================================================================= */
	/*	Timer count
	/* ========================================================================= */

	"use strict";
    $(".number-counters").appear(function () {
        $(".number-counters [data-to]").each(function () {
            var e = $(this).attr("data-to");
            $(this).delay(6e3).countTo({
                from: 50,
                to: e,
                speed: 3e3,
                refreshInterval: 50
            })
        })
    });
	
	/* ========================================================================= */
	/*	Back to Top
	/* ========================================================================= */
	
	
    $(window).scroll(function () {
        if ($(window).scrollTop() > 400) {
            $("#back-top").fadeIn(200)
        } else {
            $("#back-top").fadeOut(200)
        }
    });
    $("#back-top").click(function () {
        $("html, body").stop().animate({
            scrollTop: 0
        }, 1500, "easeInOutExpo")
    });
	
});


// ========== END GOOGLE MAP ========== //