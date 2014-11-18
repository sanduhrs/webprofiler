(function ($, Drupal, Backbone) {

  "use strict";

  Drupal.webprofiler.views.DashboardView = Backbone.View.extend({
    el: '#dashboard',

    events: {
      'click .webprofiler--panel': 'loadPanel'
    },

    initialize: function (options) {
      this.options = options || {};
    },

    loadPanel: function (e) {
      e.preventDefault();
      var id = $(e.target).attr('id');

      var collector = this.options.collections.get(id);

      if (!collector) {
        collector = new Drupal.webprofiler.models.CollectorModel({id: id});
        collector.fetch();
        this.options.collections.push(collector);
      }

      var collectorView = new Drupal.webprofiler.views.PhpConfigView({model: collector});
      collectorView.render();
    }
  });

})(jQuery, Drupal, Backbone);
