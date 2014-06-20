/**
 * @file
 * Database panel app.
 */
(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.webprofiler_database = {
        attach: function (context) {
            $('.wp-query-info-button').on('click', function (event) {
                $(this).toggleClass('open');
                $('.wp-query-info', $(this).parent()).toggle();
            });

            $('.wp-query-explain-button').on('click', function (event) {
                var position = $(this).attr('data-wp-query-position'), wrapper = $(this).parent();
                var url = Drupal.url('admin/config/development/profiler/database_explain/' + drupalSettings.webprofiler.token + '/' + position);

                $.getJSON(url, function (data) {
                    _.templateSettings.variable = "wp";

                    var template = _.template(
                        $("#wp-query-explain-template").html()
                    );

                    wrapper.html(template(data));
                });
            });

            $('#edit-query-filter').on('click', function (event) {
                var queryType = $('#edit-query-type').val(), queryCaller = $('#edit-query-caller').val();

                if (queryType != '' || queryCaller != '') {
                    $(".wp-query").each(function () {
                        $(this).hide();
                    });

                    if (queryType == '') {
                        $('*[data-wp-query-caller="' + queryCaller + '"]').show();
                    } else if (queryCaller == '') {
                        $('*[data-wp-query-type="' + queryType + '"]').show();
                    } else {
                        $('*[data-wp-query-type="' + queryType + '"][data-wp-query-caller="' + queryCaller + '"]').show();
                    }
                } else {
                    $(".wp-query").each(function () {
                        $(this).show();
                    });
                }

                return false;
            });

            hljs.initHighlightingOnLoad();
        }
    }
})(jQuery, Drupal, drupalSettings);
