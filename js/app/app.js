(function ($, Drupal) {

  "use strict";

  Drupal.behaviors.webprofiler = {
    attach: function (context) {
      var collections = new Drupal.webprofiler.collections.CollectorsCollection();
      new Drupal.webprofiler.views.DashboardView({'collections' : collections});
    }
  };

  Drupal.webprofiler = {

    // A hash of View instances.
    views: {},

    // A hash of Model instances.
    models: {},

    // A hash of Collection instances.
    collections: {}

  }

})(jQuery, Drupal);
