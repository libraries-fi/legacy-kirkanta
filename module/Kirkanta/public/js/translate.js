define(function() {
  "use strict";

  var tr = function(string) {
    var bundle = tr.bundle || {};
    return string in bundle ? bundle[string] : string;
  };

  tr.source = function(bundle) {
    this.bundle = bundle;
  };

  return tr;
});
