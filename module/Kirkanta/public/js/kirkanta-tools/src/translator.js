define(["samufw/translator", "i18n!nls/default"], function(BaseTranslator, strings) {
  "use strict";

  var Translator = BaseTranslator.extend({
    initialize: function(options) {
      this.strings = strings;
    }
  });

  return Translator;
});
