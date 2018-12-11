
require(["samufw/dom"], function(dom) {
  "use strict";

  document.documentElement.style.overflow = "auto";

  dom('form.template-select-form').forEach(function(element) {
    var form = dom(element);

    form.find("input[name='name_filter']").on("keypress", function(event) {
      if (event.keyCode == 13) {
        return event.preventDefault();
      }
    }).on("keyup", function(event) {
      form.find("div.form-group.filtered").removeClass("filtered");

      var value = event.target.value;

      if (value.length >= 3) {
        form.find("fieldset").forEach(function(fieldset) {
          dom(fieldset).find("label").forEach(function(label) {
            if (label.innerHTML.toLowerCase().indexOf(value) == -1) {
              dom(label.parentElement).addClass("filtered");
            }
          });
        });
      }
    });
  });
});
