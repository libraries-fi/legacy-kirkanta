require(["translate", "samufw/util", "samufw/dom", "kirkanta-tools/periodslider", "kirkanta-tools/daycollection"],
function(tr, util, dom, PeriodSlider, DayCollection) {
  "use strict";

  dom("#period-form").forEach(function(element) {
    var form = dom(element);
    var inputCollection = form.find("fieldset.form-collection");
    var inputBegin = form.find("[name='valid_from']");
    var inputEnd = form.find("[name='valid_until']");
    var inputContinuous = form.find("[type='checkbox'][name='continuous']");

    var collection = new DayCollection({
      source: inputCollection
    });

    var slider = new PeriodSlider;
    slider.renderTo(dom("<div id=\"period-slider-wrap\"/>"));
    form.find("#input-group-days").before(slider.parent);

    slider.on("valueChange", function(value) {
      if (value < collection.populatedLength) {
        var diff = collection.length - value;
        var message = tr("Selecting this value will remove last {0} day fields. Continue?");

        if (!confirm(util.format(message, diff))) {
          return false;
        }
      }

      collection.resize(parseInt(value));

      if (slider.count > 1) {
        slider.parent.first.style.display = "block";
      } else {
        slider.parent.first.style.display = "none";
      }
    });

    dom([inputBegin, inputEnd]).on("change", function(event) {
      var range = {
        from: inputBegin.get("value"),
        to: inputEnd.get("value"),
      };
      slider.range = range;
      collection.context = range;
    });

    inputContinuous.on("change", function() {
      if (inputContinuous.get("checked")) {
        inputEnd.set("value", "");
        slider.range = {};
      } else {
        inputEnd.trigger("change");
      }
      inputEnd.set("disabled", inputContinuous.get("checked"));
    });

    var range = {
      from: inputBegin.get("value"),
      to: inputEnd.get("value"),
      value: collection.length,
    };
    collection.context = range;
    collection._updateRowNames();

    if (inputContinuous.get("checked")) {
      inputContinuous.trigger("change");
    } else {
      slider.range = range;
    }


    var TimeInput = function(input) {
      this.input = input;
      this.input.placeholder = "––:––";
      this.input.addEventListener("keypress", this.onInput.bind(this));
    };

    TimeInput.prototype = {
      onInput: function(event) {
        if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) {
          return;
        }

        if (event.keyCode == 8) {
          // Backspace
          return;
        }

        var char = event.key || String.fromCharCode(event.keyCode);

        if (char) {
          event.preventDefault();
          var value = this.filter(this.input.value, char);
          this.input.value = value;
        }
      },
      filter: function(value, next) {
        if (next < "0" || next > "9") {
          return value;
        }
        if (value.length == 0) {
          if (next >= "3") {
            value = "0" + next;
          } else {
            value = next;
          }
        } else if ((value.length == 1 || value.length == 4) && next >= "0" && next <= "9") {
          value += next;
        } else if (value.length == 2) {
          value += ":" + next;
        } else if (value.length == 3 && next >= "0" && next <= "5") {
          value += next;
        }
        if (value.length == 2) {
          value += ":";
        }
        return value;
      }
    };

    dom("#period-form input.input-days-times-opens, #period-form input.input-days-times-closes").forEach(function(input) {
      input.type = "text";
      var manager = new TimeInput(input);
    });

    collection.on("insertRows", function(i, count) {
      var rows = collection.rows.slice(i, i + count);
      dom(rows).find("input.input-days-times-opens, #period-form input.input-days-times-closes").forEach(function(input) {
        input.type = "text";
        var manager = new TimeInput(input);
      });
    });

    collection.on("insertTimeRows", function(day, i, count) {
      var rows = collection.rows[day].times.rows.slice(i, i + count);
      dom(rows).find("input.input-days-times-opens, #period-form input.input-days-times-closes").forEach(function(input) {
        input.type = "text";
        var manager = new TimeInput(input);
      });

      dom(collection.rows[day]).removeClass("times-collection-empty");;
    });

    collection.on("removeTimeRows", function(day, i, count) {
      var days = collection.rows[day].times.rows;
      var container = dom(collection.rows[day]);

      if (days.length == 0) {
        container.addClass("times-collection-empty");
      }
    });
  });
});

// require(["samufw/dom", "samufw-inputs/calendar"],
// function(dom, Calendar) {
//   "use strict";
//
//   dom("#input-valid-from,#input-valid-until").forEach(function(input) {
//     var calendar = new Calendar({ input: input });
//     calendar.renderTo(dom("<div class=\"period-calendar-wrap\"/>"));
//     dom(input).after(calendar.parent);
//   });
// });

require(["samufw/dom", "samufw-inputs/datepicker"],
function(dom, DatePicker) {
  "use strict";

  dom("#input-valid-from,#input-valid-until").forEach(function(input) {
    var calendar = new DatePicker({ calendar: input });
    calendar.renderTo(dom("<div class=\"period-calendar-wrap\"/>"));
    dom(input).after(calendar.parent);
  });
});
