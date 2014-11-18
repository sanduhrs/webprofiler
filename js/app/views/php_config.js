(function ($, Drupal, Backbone) {

  "use strict";

  Drupal.webprofiler.views.PhpConfigView = Backbone.View.extend({
    tagName: '#php_config',

    events: {

    },

    render: function () {
      this.$el.html('php_config');
      return this;
    }
  });

})(jQuery, Drupal, Backbone);
