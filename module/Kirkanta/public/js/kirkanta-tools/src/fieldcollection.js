define(["moment", "samufw/dom", "samufw/core/observable"], function(moment, dom, Observable) {
  "use strict";

  var FieldCollection = Observable.extend({
    initialize: function(opts) {
      this.placeholder = "--index--";
      this.source = dom(opts.source);
      this.template = this.source.one("span.template").first.dataset.template;
      this.nextIndex = this.rows.length;
      this.on("initRow", this._init, this);
      // this.on("initRow", function(row, index) {
      //   dom(row).addClass("collection-row collection-row-" + index);
      // });

      this.rows.forEach(function(row, i) {
        this.trigger("initRow", row, i);
      }, this);
    },
    appendRows: function(count) {
      this.insertRows(this.length, count);
    },
    appendRow: function() {
      this.insertRows(this.length, 1);
    },
    insertRows: function(i, count) {
      count = arguments.length == 2 ? arguments[1] : 1;
      var loops = count;
      while (loops--) {
        this.container.append(this._createRow(this.length));
      }
      this.trigger('insertRows', i, count, this.rows.slice(i));
    },
    removeRows: function(pos, count) {
      count = arguments.length == 2 ? arguments[1] : 1;
      var loops = count;
      var rows = this.rows;
      while (loops--) {
        var element = rows[pos + loops];
        element.parentElement.removeChild(element);
        // this.container.first.removeChild(rows[pos + loops]);
      }
      this.trigger("removeRows", pos, count);
    },
    removeRow: function(i) {
      this.removeRows(arguments.length ? i : this.length - 1);
    },
    resize: function(size) {
      this.removeRows(size, Math.max(this.length - size, 0));
      this.insertRows(this.length, Math.min(size - this.length));
    },
    _createRow: function(i) {
      var index = this.nextIndex++;
      var template = this.template.replace(new RegExp(this.placeholder, "g"), index);
      var row = dom(template);
      this.trigger("initRow", row.first, index);
      return row;
    },
    _init: function(row, index) {

    }
  });

  Object.defineProperties(FieldCollection.prototype, {
    length: {
      get: function() {
        return this.rows.length;
      }
    },
    container: {
      get: function() {
        return this.source;
      }
    },
    rows: {
      get: function() {
        var fieldsets = [];
        Array.prototype.forEach.call(this.container.first.children, function(element) {
          if (element.tagName == "FIELDSET") {
            fieldsets.push(element);
          }
        });
        return fieldsets;
      }
    },
  });

  return FieldCollection;
});
