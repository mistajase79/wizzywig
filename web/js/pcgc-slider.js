/**
 * Prototype Creative jQuery Slider Plugin
 * http://www.prototypecreative.co.uk
**/

//$(document).ready(function($) {
$(window).load(function() {
  $('.pcgc-slider-container').each(function() {

    var isBool = function(val,def) {
      if (typeof val === 'undefined') {
        return def;
      }

      var lower = val.toLowerCase();

      switch(lower) {
          case 'true':
            return true;
          case 'false':
            return false;
          default:
            return def;
      }
    };

    // default settings
    var config             = {
      width                : '100%',
      type                 : $(this).attr('data-type') || 'slide',
      speed                : parseInt($(this).attr('data-speed'),10) || 450,
      duration             : parseInt($(this).attr('data-duration'),10) || 8000,
      automatic            : isBool($(this).attr('data-automatic'),true),
      showcontrols         : isBool($(this).attr('data-showcontrols'),true),
      centercontrols       : isBool($(this).attr('data-centercontrols'),true),
      nexttext             : $(this).attr('data-nexttext') || 'Next',
      previoustext         : $(this).attr('data-previoustext') || 'Prev',
      showmarkers          : isBool($(this).attr('data-showmarkers'),true),
      centermarkers        : isBool($(this).attr('data-centermarkers'),true),
      hoverpause           : isBool($(this).attr('data-hoverpause'),true),
      usecaptions          : isBool($(this).attr('data-usecaptions'),false),
      custommarkerlocation : $(this).attr('data-custommarkerlocation') || '',
      customcontrollocation : $(this).attr('data-customcontrollocation') || ''
    };

    // define the slider element variables we need
    var $wrapper           = $(this),
      $slider              = $wrapper.find('ul:first'),
      $slides              = $slider.children('li'),
      $controls_wrapper    = null,
      $controls_next       = null,
      $controls_prev       = null,
      $markers_wrapper     = null,
      $markers_markers     = null,
      $canvas              = null,
      $clone_first         = null,
      $clone_last          = null;

    // object to store current states
    var current            = {
      slidecount           : $slides.length,
      animating            : false,
      paused               : false,
      currentslide         : 1,
      nextslide            : 2,
      currentindex         : 0,
      nextindex            : 1,
      interval             : null
    };

    var responsive         = {
      width                : null,
      ratio                : null
    };

    // initialise settings
    var init = function() {
      // add class to ul for styling
      $slider.addClass('pcgc-slider');

      // add class to li for styling
      $slides.addClass('pcgc-slide');

      init_responsive();

      // configurations that are only avaliable if there's more than 1 slide
      if (current.slidecount > 1 ) {

        // create and show controls
        if (config.showcontrols) {
          init_controls();
        }

        // create and show markers
        if (config.showmarkers) {
          init_markers();
        }

        // enable pause on hover
        if (config.hoverpause && config.automatic){
          init_hoverpause();
        }

        // config slide animation type
        if (config.type === 'slide'){
          init_slide();
        }

      } else {
        // Stop automatic animation, because we only have one slide
        config.automatic = false;
      }

      if (config.usecaptions) {
        init_captions();
      }

      // show slides
      $slider.show();
      $slides.eq(current.currentindex).show();

      // If automatic is set to true start interval
      if (config.automatic) {
        current.interval = setInterval(function () {
          transition('forward', false);
        }, config.duration);
      }

      var swipedir,
      startX,
      startY,
      distX,
      distY,
      threshold = 50, // required min distance to be considered a swipe
      restraint = 30, // max distance allowed at the same time in opposite axis
      allowedTime = 1000, // max time allowed between touch start/end
      elapsedTime,
      startTime;

      $wrapper.on('touchstart', 'img', function(e){
        var touchobj = e.originalEvent.changedTouches[0];
        swipedir = 'none';
        dist = 0;
        startX = touchobj.pageX;
        startY = touchobj.pageY;
        startTime = new Date().getTime(); // record time when touch starts
        e.preventDefault();
      });

      $wrapper.on('touchmove', 'img', function(e){
        e.preventDefault(); // prevent page scrolling when inside our element
      });

      $wrapper.on('touchend', 'img', function(e){
        var touchobj = e.originalEvent.changedTouches[0];
        distX = touchobj.pageX - startX;
        distY = touchobj.pageY - startY;
        elapsedTime = new Date().getTime() - startTime;

        if (elapsedTime <= allowedTime){

          if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint){
            swipedir = (distX < 0)? 'left' : 'right';
          } else if (Math.abs(distY) >= threshold && Math.abs(distX) <= restraint){
            swipedir = (distY < 0)? 'up' : 'down';
          }

        }

        if (swipedir === 'right') {
          transition('previous',false);
        } else if (swipedir === 'left') {
          transition('forward',false);
        }

        e.preventDefault();
      });
    };

    var init_responsive = function() {
      // set sizes
      responsive.width    = '100%';

      if (config.type === 'fader') {
        $wrapper.addClass('fader');

        /*
        var h = $slider.find('li:first').find('img:first').height();
        $slider.css({
            'height'              : h
        });
        */

        $(window).resize(function() {
          resize_done(function(){
            $slider.removeClass('hidden');
            var h = $slider.find('li:first').find('img:first').height();
            $slider.css({
              'height'              : h
            });
          }, 200, "responsiveTimer");
        });

      } else {
        $wrapper.addClass('slider');
      }

      if (config.type === 'slide') {
        // calculate and update dimensions and set on initial load
        responsive.width        = $wrapper.outerWidth();

        $slides.css({
          'width'             : responsive.width
        });
        $slider.css({
          'width'             : responsive.width * current.slidecount
        });


        $(window).resize(function() {
          // calculate and update dimensions
          responsive.width    = $wrapper.outerWidth();
          $slides.css({
            'width'         : responsive.width
          });

          $slider.css({
            'width'         : responsive.width * current.slidecount
          });

          resize_done(function(){
            transition(false,2,true);
          }, 200, "responsiveTimer");
        });
      }
    };

    var resize_done = (function () {
      var timers = {};
      return function (callback, ms, timerId) {

        if (!timerId) {
          timerId = "id not set";
        }

        if (timers[timerId]) {
          clearTimeout (timers[timerId]);
        }

        timers[timerId] = setTimeout(callback, ms);
      };
    })();

    var init_slide = function() {
      // create a clone of the first and last slides
      $clone_first    = $slides.eq(0).clone();
      $clone_last     = $slides.eq(current.slidecount-1).clone();

      // add them to the DOM
      $clone_first.attr({'data-clone' : 'last', 'data-slide' : 0}).appendTo($slider).show();
      $clone_last.attr({'data-clone' : 'first', 'data-slide' : 0}).prependTo($slider).show();

      // update the elements object
      $slides               = $slider.children('li');
      current.slidecount    = $slides.length;

      // create a 'canvas' element which is neccessary for the slide animation to work
      $canvas = $('<div class="pcgc-slider-wrapper"></div>');

      // update the dimensions to the slider to accomodate all the slides side by side
      $slider.css({
          'width'     : responsive.width * (current.slidecount + 2),
          'marginLeft'      : -responsive.width * current.currentslide
      });

      // add to the DOM and move the slider inside
      $canvas.prependTo($wrapper);
      $slider.appendTo($canvas);

    };

    var init_controls = function() {
      // create the controls
      $controls_wrapper  = $('<ul class="pcgc-slider-controls"></ul>');
      //$controls_next     = $('<li class="pcgc-slider-next"><a href="#" data-direction="forward">' + config.nexttext + '</a></li>');
      //$controls_prev     = $('<li class="pcgc-slider-prev"><a href="#" data-direction="previous">' + config.previoustext + '</a></li>');

      $controls_prev     = $('<a href="#" data-direction="previous"><span class="slide-left">Previous</span></a>');

      $controls_next     = $('<a href="#" data-direction="forward"><span class="slide-right">Next</span></a>');

      // bind click events
      $controls_wrapper.on('click','a',function(e){
        e.preventDefault();
        var direction = $(this).attr('data-direction');

        if (!current.animating) {

          if (direction === 'forward') {
              transition('forward',false);
          }

          if (direction === 'previous') {
              transition('previous',false);
          }

        }

      });

      // add to DOM
      $controls_prev.appendTo($controls_wrapper);
      $controls_next.appendTo($controls_wrapper);

      if (config.customcontrollocation.length) {
        // add to custom location
        $controls_wrapper.appendTo(config.customcontrollocation);
      } else {
        // add to regular wrapper
        $controls_wrapper.appendTo($wrapper);
      }

      // vertically center the controls if required
      if (config.centercontrols) {
        // calculate offset % for vertical positioning
        var offset_px = $controls_next.children('a').outerHeight() / 2;
        $controls_next.find('a').css({'position':'absolute','right': 0,'top':'50%','marginTop': -offset_px});
        $controls_prev.find('a').css({'position':'absolute','left': 0,'top':'50%','marginTop': -offset_px});
      }

    };

    var init_markers = function() {

      // create a wrapper for markers
      $markers_wrapper = $('<ul class="pcgc-slider-markers"></ul>');

      // for each slide
      $.each($slides, function(key, slide){
        var slidenum    = key + 1,
            gotoslide   = key + 1;

        if (config.type === 'slide') {
          // add an additional 2 to account for clones
          gotoslide = key + 2;
        }

        var marker = $('<li><a href="#">'+ slidenum +'</a></li>');

        // set the first marker active
        if (slidenum === current.currentslide) {
          marker.addClass('active');
        }

        // bind a click event
        marker.on('click','a',function(e){
          e.preventDefault();

          if (!current.animating && current.currentslide !== gotoslide) {
            transition(false,gotoslide);
          }

        });

        // add the marker to the wrapper
        marker.appendTo($markers_wrapper);
      });

      // add markers to DOM - location depends on user setting
      if (config.custommarkerlocation.length) {
        // add to custom location
        $markers_wrapper.appendTo(config.custommarkerlocation);
      } else {
        // add to regular wrapper
        $markers_wrapper.appendTo($wrapper);
      }

      $markers_markers = $markers_wrapper.find('li');

      // center the markers
      if (config.centermarkers) {
        $markers_wrapper.addClass('center');
        var offset = ($wrapper.outerWidth() - $markers_wrapper.width()) / 2;
        $markers_wrapper.css('left', offset);
      }

    };

    var init_hoverpause = function() {
      $wrapper.hover(function () {

        if (!current.paused) {
          clearInterval(current.interval);
          current.paused = true;
        }

      }, function () {

        if (current.paused) {
          current.interval = setInterval(function () {
            transition('forward', false);
          }, config.duration);
          current.paused = false;
        }

      });
    };

    var init_captions = function() {
      $.each($slides, function (key, slide) {
        var caption = $(slide).children('img:first-child').attr('alt');

        // Account for images wrapped in links
        if (!caption) {
          caption = $(slide).children('a').find('img:first-child').attr('alt');
        }

        if (caption) {
          caption = $('<p class="pcgc-slider-caption">' + caption + '</p>');
          caption.appendTo($(slide));
        }

      });
    };

    var set_next = function(direction) {

      if (direction === 'forward'){

        if ($slides.eq(current.currentindex).next().length){
          current.nextindex = current.currentindex + 1;
          current.nextslide = current.currentslide + 1;
        } else {
          current.nextindex = 0;
          current.nextslide = 1;
        }

      } else {

        if ($slides.eq(current.currentindex).prev().length){
          current.nextindex = current.currentindex - 1;
          current.nextslide = current.currentslide - 1;
        } else {
          current.nextindex = current.slidecount - 1;
          current.nextslide = current.slidecount;
        }

      }

    };

    var transition = function(direction, position, override) {
      if (typeof override === 'undefined') {
        override = false;
      }

      if (override) {
        current.animating = false;
      }

      // only if we're not already doing things
      if (!current.animating) {
        // doing things
        current.animating = true;

        if (position) {
          current.nextslide = position;
          current.nextindex = position-1;
        } else {
          set_next(direction);
        }

        // fade animation
        if (config.type === 'fader') {

            if (config.showmarkers) {
              $markers_markers.removeClass('active');
              $markers_markers.eq(current.nextindex).addClass('active');
            }

            // fade out current
            //$slides.eq(current.currentindex).fadeOut(config.speed);
            $slides.eq(current.currentindex)
                   .animate({'opacity': 0}, config.speed)
                   .css({ 'z-index' : 0 });

            // fade in next
            //$slides.eq(current.nextindex).fadeIn(config.speed, function(){
            $slides.eq(current.nextindex)
                   .animate({'opacity': 1}, config.speed, function(){
                      // update state variables
                      current.animating = false;
                      current.currentslide = current.nextslide;
                      current.currentindex = current.nextindex;
                   })
                   .css({ 'z-index' : 1 });
        }

        // slide animation
        if (config.type === 'slide') {

          if (config.showmarkers) {
            var markerindex = current.nextindex-1;

            if (markerindex === current.slidecount-2) {
              markerindex = 0;
            } else if (markerindex === -1) {
              markerindex = current.slidecount-3;
            }

            $markers_markers.removeClass('active');
            $markers_markers.eq(markerindex).addClass('active');
          }

          current.slidewidth = responsive.width;

          $slider.animate({'marginLeft': -current.nextindex * current.slidewidth }, config.speed, function(){
            current.currentslide = current.nextslide;
            current.currentindex = current.nextindex;

            // is the current slide a clone?
            if ($slides.eq(current.currentindex).attr('data-clone') === 'last') {
              // affirmative, at the last slide (clone of first)
              $slider.css({'marginLeft': -current.slidewidth });
              current.currentslide = 2;
              current.currentindex = 1;
              $('.testimonial-pagination span').text('1');
            } else if ($slides.eq(current.currentindex).attr('data-clone') === 'first') {
              // affirmative, at the fist slide (clone of last)
              $slider.css({'marginLeft': -current.slidewidth *(current.slidecount - 2)});
              current.currentslide = current.slidecount - 1;
              current.currentindex = current.slidecount - 2;
              $('.testimonial-pagination span').text($slides.length - 2);
            } else {
              $('.testimonial-pagination span').text(current.currentindex);
            }

            current.animating = false;
          });
        }

      }

    };
    // run init function
    init();
    $(window).resize();
  });
});
