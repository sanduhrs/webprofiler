(function (Drupal, Backbone, drupalSettings) {

  "use strict";

  Drupal.webprofiler.models.CollectorModel = Backbone.Model.extend({
    urlRoot : '/admin/reports/profiler/panel/' + drupalSettings.webprofiler.token
  });

})(Drupal, Backbone, drupalSettings);
