define(["moment", "samufw/dom", "./fieldcollection"],
function(moment, dom, FieldCollection) {
  "use strict";

  var TimesCollection = FieldCollection.extend({
    _init: function(row) {
      var row = dom(row);
      row.find("button.input-days-times-add-time").on("click", this.appendRow.bind(this));
      row.find("button.input-days-times-remove-time").on("click", function(event) {
        var i = dom(event.target).closest(".times-row").index();
        this.removeRow(i);
      }, this);
    }
  });

  Object.defineProperties(TimesCollection.prototype, {
    container: {
      get: function() {
        return this.source.find(".day-times");
      }
    },
    rows: {
      get: function() {
        return this.container.find("div.times-row").elements;
      }
    },
  });

  return TimesCollection;
});
