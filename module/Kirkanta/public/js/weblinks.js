require(["samufw/dom"], function(dom) {
  "use strict";

  var button = dom('<button type="button" class="btn btn-link">New group...</button>').on("click", function(event) {
    console.log("CLICK");
  });

  dom(".link-group-select").after(button);
});
