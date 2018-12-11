define(["samufw/dom", "samufw/core/observable"], function(dom, Observable) {
  "use strict";

  var Accordion = Observable.extend({
    initialize: function(options) {
      this.collection = options.collection;
      this.collection.rows.forEach(this.initRow, this);
      this.collection.on("initRow", this.initRow, this);
      this.collection.on("insertRows", function(i, count, rows) {
        dom(rows).addClass("expanded");
      });
    },
    initRow: function(fieldset, i) {
      var row = dom(fieldset);

      row.find(".accordion-delete-row").on("click", function(event) {
        var target = dom(event.currentTarget.value);
        var index = this.collection.rows.indexOf(target.first);
        this.collection.removeRow(index);
      }, this);
      row.find(".accordion-expand-row").on("click", function(event) {
        event.preventDefault();
        this.toggleRow(row);
      }, this);
    },
    toggleRow: function(row) {
      row.toggleClass("expanded");
    }
  });

  return Accordion;
});
