define(["moment", "translate", "samufw/util", "samufw/view", "text!./templates/slider.html", "i18n!nls/default"], function(moment, tr, util, View, template, strings) {
  "use strict";

  tr.source(strings);

  var PeriodSlider = View.extend({
    template: template,
    events: {
      "mousedown .lds-grain": "onDragStart",
      "mouseup": "onDragStop",
      "mousemove": "onDragMove",
      "click .slider-value": "onChange",
    },
    initialize: function() {
      this._grain = null;
      this.dragging = false;
      this.startPos = -1;
      this.snap = 40;

      this.vars = {
        name: this.randomName(),
        options: {}
      };

      this.on("render", function() {
        this._grain = null;
        this._count = null;
      });

      this.updateOptions();
    },
    randomName: function() {
      return "slider-" + (Math.round(Math.random() * 10000) + 10000);
    },
    onDragStart: function(event) {
      if (!this.dragging) {
        event.preventDefault();
        this.dragging = true;
        this.startPos = event.clientX;

        var grain = this.grain;
        grain.style.left = grain.get("offsetLeft") + "px";
        grain.style.marginLeft = 0;
      }
    },
    onDragStop: function(event) {
      event.preventDefault();
      this.dragging = false;

      var i = this.closestIndex(this.dragPos());
      this.index = i;
    },
    onDragMove: function(event) {
      if (this.dragging && this.startPos >= 0) {
        event.preventDefault();
        var delta = event.clientX - this.startPos;

        if (Math.abs(delta) > 1) {
          this.grain.style.marginLeft = delta + "px";
        }
      }
    },
    dragPos: function() {
      var pos = this.grain.get("offsetLeft") - this.root.get("offsetLeft") + this.grain.get("offsetWidth")/2;
      return pos;
    },
    onChange: function(event) {
      event.preventDefault();
      var input = event.target;
      var index = this.dom(input).parent.index();

      this.index = index;
//       this.trigger("valueChange", input.getAttribute("data-value"));
      this.trigger("change", event);
    },
    closestIndex: function(pos) {
      for (var i = 0; i < this.count; i++) {
        var offset = this.width / (this.count*2) * (i*2 + 1);
        var delta = Math.abs(offset - pos);
        if (delta < this.snap) {
          return i;
        }
      }
      return this.index;
    },
    moveToIndex: function(index) {
      var offset = this.width / (this.count*2) * (index*2 + 1);
      offset += this.root.get("offsetLeft") - this.grain.get("offsetWidth")/2;

      this.grain.style.marginLeft = offset + "px";
      this.grain.style.left = 0;
    },
    moveToEventPos: function(event) {
      this.index = this.closestIndex(event.clientX - this.root.get("offsetLeft"));
    },
    updateOptions: function(from, to) {
      if (from && to) {
        from = moment(from);
        to = moment(to);
        var delta = to.diff(from, "days") + 1;
        var weeks = Math.min(Math.floor(delta / 7), 4);
        var options = {};

        if (delta > 6) {
          for (var i = 1; i <= weeks; i++) {
            options[i*7] = (i == 1) ? tr("1 week") : util.format(tr("{0} weeks"), i);
          }
        }

        if (delta <= 6 || (weeks <= 4 && delta % 7)) {
          options[delta] = tr("Full");
        }

        this.vars.options = options;

      } else {
        var label = tr("{0} weeks");
        this.vars.options = {
          7: tr("1 week"),
          14: util.format(label, 2),
          21: util.format(label, 3),
          28: util.format(label, 4),
        };
      }
    },
    _closestIndexForValue: function(value) {
      var values = Object.keys(this.vars.options).reverse();
      for (var i = values.length-1; i >= 0; i--) {
        var current = parseInt(value, 10);
        var test = parseInt(values[i], 10);
        if (test <= current) {
          return values.length - 1 - i;
        }
      };
      return 0;
    }
  });

  Object.defineProperties(PeriodSlider.prototype, {
    grain: {
      get: function() {
        if (!this._grain) {
          this._grain = this.root.find('.lds-grain');
        }
        return this._grain;
      }
    },
    count: {
      get: function() {
        if (!this._count) {
          this._count = this.root.find('.lds-ticks li').length;
        }
        return this._count;
      }
    },
    width: {
      get: function() {
        return this.root.get("offsetWidth");
      }
    },
    value: {
      get: function() {
        return this._current.first.getAttribute("data-value");
      },
      set: function(value) {
        var input = this.root.find('.slider-value[data-value="' + value + '"]');

        if (input.length > 0) {
          input.parent.siblings().find("a").removeClass("active");
          input.addClass("active");
          this.moveToIndex(input.parent.index());
          this.trigger("valueChange", input.first.getAttribute("data-value"));
        } else {
          this.index = this._closestIndexForValue(value);
        }
      }
    },
    index: {
      get: function() {
        return this._current.parent.index();
      },
      set: function(i) {
        var input = this.root.find('.lds-ticks li:nth-child(' + (i+1) + ') .slider-value');
        if (input.length > 0) {
          this.value = input.first.getAttribute("data-value");
        } else if (i != 0) {
          this.index = 0;
        }
      }
    },
    range: {
      get: function() {
        return this._range || {};
      },
      set: function(range) {
        var value = "value" in range ? range.value : this.value;
        this._range = range;
        this.updateOptions(range.from, range.to);

        if (typeof value == "undefined") {
          value = Object.keys(this.vars.options)[0];
        }

        this.render().done(function() {
          this.value = value;
        }, this);
      }
    },
    _current: {
      get: function() {
        return this.root.find('.slider-value.active');
      }
    },
  });

  return PeriodSlider;
});
