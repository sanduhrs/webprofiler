/**
 * @file
 * Some basic behaviors and utility functions for Views.
 */
(function ($, Drupal, drupalSettings) {

    Drupal.behaviors.webprofiler = {
        attach: function (context) {
            $('.query-info-button').click(function () {
                $(this).toggleClass('open');
                $('.query-data', $(this).parent()).toggle();
            });
        }
    }

})(jQuery, Drupal, drupalSettings);