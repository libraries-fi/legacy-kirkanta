
require(["samufw/dom"], function(dom) {
  dom('#organisation-form').forEach(function(form) {
    var form = dom(form);

    /*
     * Disable branch_type select when it isn't allowed.
     */
    var inputType = form.find("select[name='type']");
    var inputBranchType = form.find("select[name='branch_type']");

    inputType.on("change", function() {
      inputBranchType.prop("disabled", inputType.get("value") != "library");
    }).trigger("change");

    /*
     * Disable mail address field when it isn't used.
     */
    var inputUseAddress = form.find("input[type='checkbox'][name$='mail_address[enabled]']");
    var addressFields = dom(inputUseAddress.parent).siblings().find("input");
    inputUseAddress.on("change", function() {
      addressFields.prop("disabled", !inputUseAddress.prop("checked"));
    }).trigger("change");
  });
});
