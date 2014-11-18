(function (Drupal, Backbone) {

  "use strict";

  Drupal.webprofiler.collections.CollectorsCollection = Backbone.Collection.extend({
    model: Drupal.webprofiler.models.CollectorModel
  });

})(Drupal, Backbone);
