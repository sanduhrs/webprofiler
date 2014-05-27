/**
 * @file
 * Database panel app.
 */
(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.webprofiler_database = {
        attach: function (context) {
            $('.query-info-button').click(function () {
                $(this).toggleClass('open');
                $('.query-data', $(this).parent()).toggle();
            });

            $('.query-explain-button').click(function () {
                var position = $(this).attr('data-query-position'), wrapper = $(this).parent();
                var url = Drupal.url('admin/config/development/profiler/database_explain/' + drupalSettings.webprofiler.token + '/' + position);

                $.getJSON(url, function (data) {
                    _.templateSettings.variable = "rc";

                    var template = _.template(
                        $("#query-explain-template").html()
                    );

                    wrapper.html(template(data));
                });

            });
        }
    }
})(jQuery, Drupal, drupalSettings);
