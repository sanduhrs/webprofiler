/**
 * @file
 * Some basic behaviors and utility functions for webprofiler.
 */
(function ($, Drupal, drupalSettings) {

    Drupal.behaviors.webprofiler = {
        attach: function (context) {
            var $context = $(context);

            $('.query-info-button').click(function () {
                $(this).toggleClass('open');
                $('.query-data', $(this).parent()).toggle();
            });

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