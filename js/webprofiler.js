/**
 * @file
 * Some basic behaviors and utility functions for webprofiler.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.behaviors.webprofiler = {
    attach: function (context) {
      $('.vertical-tabs-panes .vertical-tabs-pane').each(function () {
        var id = $(this).attr('id');
        var summary = $('.summary', $(this)).html();
        $(this).drupalSetSummary(function (context) {
          return summary;
        });
      });

    }
  }

})(jQuery, Drupal, drupalSettings);
