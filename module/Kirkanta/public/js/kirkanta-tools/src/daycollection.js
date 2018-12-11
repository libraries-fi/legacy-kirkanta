define(["moment", "samufw/dom", "./fieldcollection", "./timescollection"],
function(moment, dom, FieldCollection, TimesCollection) {
  "use strict";

  var DayCollection = FieldCollection.extend({
    initialize: function() {
      this.on("insertRows", function(i, count) {
        this._updateRowNames();
      });
      this.on("insertRows", function(i, count) {
        this._updateRowNames();
      });
    },
    _updateRowNames: function() {
      var days = this._dayNames();
      this.source.find("label.day-name").forEach(function(label, i) {
        dom(label).text(days[i]);
      });
    },
    _dayNames: function() {
      var date = moment(this.context.from || moment().startOf("isoWeek"));
      var length = this.length;
      var days = new Array(length);
      var format = "dddd DD.MM.";

      if (length >= 7) {
        date = date.startOf("isoWeek");
        format = "dddd";
      }

      for (var i = 0; i < length; i++) {
        days[i] = date.format(format);
        date.add(1, "days");
      }
      return days;
    },
    _init: function(row, i) {
      var row = dom(row);

      row.find("button.toggle-edit-days-info").on("click", function(event) {
        dom(event.target.parentElement).toggleClass("enabled");
      });

      row.find("button.input-days-insert-first-time").on("click", function(event) {
        var target_id = event.target.value;
        var i = parseInt(target_id.substring(5, target_id.length - 1));

        row.first.times.appendRow();
      });

      row.first.times = new TimesCollection({source: row.find(".times-collection").first});
      row.first.times.on("insertRows", function(pos, count) {
        this.trigger("insertTimeRows", i, pos, count);
      }, this);
      row.first.times.on("removeRows", function(pos, count) {
        this.trigger("removeTimeRows", i, pos, count);
      }, this);
    }
  });

  Object.defineProperties(DayCollection.prototype, {
    rows: {
      get: function() {
        var fieldsets = [];
        var container = this.source.find("div.container-fluid").first;
        Array.prototype.forEach.call(container.children, function(element) {
          if (element.tagName == "FIELDSET") {
            fieldsets.push(element);
          }
        });
        return fieldsets;
      }
    },
    container: {
      get: function() {
        if (!("_container" in this)) {
          this._container = dom(this.source.find("div.container-fluid").first);
        }
        return this._container;
      }
    },

    /*
     * Returns count of active rows. This means every row upto the last
     * populated row, including all empty rows preceding it.
     *
     * This is mainly used for checking how many rows can be removed from the end
     * without losing data.
     */
    populatedLength: {
      get: function() {
        var rows = this.rows;

        for (var i = rows.length; i > 0; i--) {
          var fieldset = rows[i - 1];
          var times = dom(fieldset).find("input.input-days-times-opens,input.input-days-times-closes");
          var closed = dom(fieldset).find("input[type='checkbox']");

          if (times.length && (times.raw[0].value || times.raw[1].value)) {
            return i;
          }
        };

        return 0;
      }
    },
  });

  return DayCollection;
});
