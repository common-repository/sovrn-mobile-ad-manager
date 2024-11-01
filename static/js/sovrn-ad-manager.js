'use strict';

(function($) {
    var SovrnAdManager = {
        init: function() {
            var self = this;
            $('.sovrn-ad').each(function(index, el) {
                var $el = $(el);
                self.adjustPosition($el);
                self.addCloseButton($(el));
            });
        },

        adjustPosition: function($el) {
            if ($el.hasClass('sovrn-ad-fixed') && $el.hasClass('sovrn-ad-fixed-center')) {
                var offset;
                if ($el.hasClass('sovrn-ad-fixed-left') || $el.hasClass('sovrn-ad-fixed-right')) {
                    var height = parseInt($el.height());
                    var attr = 'margin-top';
                    var offset = -1 * Math.round(height/2);
                } else {
                    var width = parseInt($el.width());
                    var attr = 'margin-left';
                    var offset = -1 * Math.round(width/2);
                }
                $el.css(attr, offset);
            }
        },

        addCloseButton: function($ad) {
            var $button = this.createCloseButton();
            $ad.find('.sovrn-ad-inner').prepend($button);
        },

        createCloseButton: function() {
            return $('<a>')
                .attr({
                    'href': '#',
                    'class': 'sovrn-ad-close-button'
                })
                .html('&times; Close')
                .click(function(e) {
                    e.preventDefault();
                    $(this)
                        .parents('.sovrn-ad')
                        .remove();
                });
        }
    };

    $(document).ready(function() {
        SovrnAdManager.init();
    });
})(jQuery);