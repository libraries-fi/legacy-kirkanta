require(["samufw/dom"], function(dom) {
  "use strict";

  dom("form.service-tool input.input-extra-preserve-name").forEach(function(input) {
    /*
     * NOTE: This ad-hoc script requires that the only text inputs on the sub form are name fields.
     *
     * There is no exact method for selecting only the name fields.
     */
    var container = dom(input).closest("fieldset");
    var name_fields = container.find("input[type='text']");
    dom(input).on("change", function(event) {
      name_fields.prop("disabled", !input.checked);
    });
  });
});
