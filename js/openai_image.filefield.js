(function ($, Drupal) {
  "use strict";
  var openaiFileField = window.openaiFileField = {};
  Drupal.behaviors.openaiFileField = {
    attach: function (context, settings) {
      var i;
      var el;
      var $els = $('.openai_image-filefield-paths', context).not('.iff-processed').addClass('iff-processed');
      for (i = 0; el = $els[i]; i++) {
        openaiFileField.processInput(el);
      }
    }
  };
  openaiFileField.processInput = function (el) {
    var widget;
    var url = el.getAttribute('data-openai_image-url');
    var fieldId = el.getAttribute('data-drupal-selector').split('-openai_image-paths')[0];
    if (url && fieldId) {
      url += (url.indexOf('?') === -1 ? '?' : '&') + 'sendto=openaiFileField.sendto&fieldId=' + fieldId;
      widget = $(openaiFileField.createWidget(url)).insertBefore(el.parentNode)[0];
      widget.parentNode.className += ' openai-filefield-parent';
    }
    return widget;
  };
  openaiFileField.createWidget = function (url) {
    var $link = $('<a class="openai-filefield-link openopenai" style="display: none">' + Drupal.t('Open openai Window') + '</a>');
    $link.attr('href', url).click(openaiFileField.eLinkClick); //remove
    return $('<div class="openai-filefield-widget"></div>').append($link)[0];
  };

  openaiFileField.eLinkClick = function (e) {
    e.preventDefault();
  };

})(jQuery, Drupal);
