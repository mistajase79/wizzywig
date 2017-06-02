/**
 * https://github.com/ProtoGit/jquery.slider
 */
(function (root, factory) {

    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(root.jQuery);
    }

})(this, function($) {

    'use strict';

    $.fn.slider = function(options) {

        if (!options) {
            options = {};
        }

        var duration = options.duration || 200;
        var delay = options.delay || 4000;
        var startPosition = options.startPosition || 0;
        var groupTogether = options.groupTogether || false;
        var onTransition = options.onTransition || null;
        var automatic = (typeof options.automatic === 'undefined') ? true : options.automatic;

        function Slider(container) {
            this.container = container;
            this.init();
        }

        Slider.prototype.init = function() {
            this.slides = this.container.find('.slides');
            this.slideCount = this.calculateSlideCount();
            this.animating = false;
            this.maxPosition = this.calculateMaxPosition();
            this.move(startPosition);
            if (automatic) {
                this.startTimer();
            }
        };

        Slider.prototype.left = function() {
            if (this.position > 0) {
                this.move(this.position - 1);
            } else {
                this.move(this.maxPosition);
            }
        };

        Slider.prototype.right = function() {
            if (this.position < this.maxPosition) {
                this.move(this.position + 1);
            } else {
                this.move(0);
            }
        };

        Slider.prototype.move = function(position, forceImmediately) {
            forceImmediately = (typeof forceImmediately !== 'undefined') ? forceImmediately : false;
            if (!this.animating || forceImmediately) {
                var self = this;
                var leftValue = (position * this.getSlideWidth()) * -1;
                this.position = position;
                if (forceImmediately) {
                    this.slides.stop().css('left', leftValue);
                    this.animating = false;
                } else {
                    this.animating = true;
                    this.slides.stop().animate({left: leftValue}, duration, function() {
                        self.animating = false;
                        if (onTransition) {
                            onTransition(position);
                        }
                    });
                }
            }
        };

        Slider.prototype.getSlideWidth = function() {
            return parseInt(this.slides.first().children().first().css('width'), 10);
        };

        Slider.prototype.calculateSlideCount = function () {
            return parseInt(this.slides.first().children().length, 10);
        };

        Slider.prototype.calculateMaxPosition = function() {
            return this.calculateSlideCount() - 1;
        };

        Slider.prototype.startTimer = function() {
            var self = this;
            this.timer = setInterval(function() {
                self.right();
            }, delay);
        };

        Slider.prototype.stopTimer = function() {
            if (this.timer) {
                clearInterval(this.timer);
            }
        };

        function buildSlider(container) {
            var slider = new Slider(container);
            container.find('.slide-left').click(function() {
                slider.stopTimer();
                slider.left();
            });
            container.find('.slide-right').click(function() {
                slider.stopTimer();
                slider.right();
            });
            return slider;
        }

        if (groupTogether) {
            return buildSlider($(this));
        } else {
            var sliders = [];
            this.each(function() {
                sliders.push(buildSlider($(this)));
            });
            return sliders;
        }

    };

});