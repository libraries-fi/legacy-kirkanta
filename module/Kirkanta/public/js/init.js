require(["moment"], function(moment) {
  moment.locale(document.documentElement.lang);
});

require(["samufw/view", "handlebars"],
function(View, Handlebars) {
  "use strict";

  View.configure({
    engine: {
      render: function(template, data) {
        return Handlebars.compile(template)(data);
      }
    },
  });
});

define("translator", ["kirkanta-tools/translator"], function(Translator) {
  var translator = new Translator;
  return translator;
});

require(["samufw/dom", "samufw/util"],
function($, util) {
  "use strict";

  $(".tr-form").forEach(function(form, i) {
    var form = $(form);
    form.find("[name='language']").on("change", function() {
      var locale = $(this).first.value;
      var groups = form.find(".tr-group");

      if (locale == "all") {
        groups.addClass("show-all");
      } else {
        groups.removeClass("show-all");
        form.find('.tr-active').removeClass('tr-active');
        form.find(util.format('.tr-locale-{0}', locale)).addClass('tr-active');
      }
    });
  });
});

require(["samufw/dom", "samufw-inputs/select"],
function(dom, Select) {
  "use strict";

  dom(".search-form select[multiple]").forEach(function(elem) {
    var widget = new Select({input: elem});

    widget.render().done(function(html) {
      dom(elem).addClass('sr-only').after(html.raw);
    });
  });
});

require(["ckeditor", "samufw/dom"],
function(ckeditor, dom) {
  "use strict";

  // console.log("CKE", ckeditor);

  dom("textarea.richtext").forEach(function(input) {
    ckeditor.replace(input, {
      customConfig: false,
      defaultLanguage: "fi",
      language: document.documentElement.lang,
      toolbarGroups: [
    		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
    		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
    		{ name: 'links', groups: [ 'links' ] },
    		{ name: 'insert', groups: [ 'insert' ] },
    		{ name: 'forms', groups: [ 'forms' ] },
    		{ name: 'tools', groups: [ 'tools' ] },
    		{ name: 'others', groups: [ 'others' ] },
    		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
    		{ name: 'colors', groups: [ 'colors' ] },
    	],
      removeButtons: 'Underline,Subscript,Superscript,Cut,Undo,Scayt,HorizontalRule,Maximize,Copy,Paste,PasteText,PasteFromWord,Redo',

      // Disable umlaut encoding.
      entities: false,
    });
  });
});

require(["samufw/dom", "kirkanta-tools/fieldcollection", "kirkanta-tools/collectionaccordion", "translator"],
function(dom, FieldCollection, Accordion, translator) {
  "use strict";

  dom("fieldset.form-collection.input-custom-data").forEach(function(fieldset) {
    var source = dom(fieldset);
    var collection = new FieldCollection({
      source: source,
    });
    var accordion = new Accordion({
      collection: collection,
    });
    var label = translator.translate("New entry");
    var addButton = dom('<button type="button" class="add-custom-data btn btn-success btn-sm">' + label + '</button>');

    source.prepend(addButton);

    addButton.on("click", function(event) {
      collection.appendRow();
    });
  });

  dom("fieldset.form-collection.dynamic-collection").forEach(function(fieldset) {
    var source = dom(fieldset);
    var collection = new FieldCollection({
      source: source,
    });
    var accordion = new Accordion({
      collection: collection,
    });
    var label = translator.translate("New entry");
    var addButton = dom('<button type="button" class="add-custom-data btn btn-success btn-sm">' + label + '</button>');

    source.prepend(addButton);

    addButton.on("click", function(event) {
      collection.appendRow();
    });
  });
});

require(["$", "translator"], function($, translator) {
  function run_ptv_sync(form) {
    if (form.dataset.ptvTrigger) {
      var buttons = $(form).find(":button").prop("disabled", true);
      $("#status-messages").empty().append($("<div/>").spinner());

      setTimeout(function() {
        $.post(form.action).then(function(result) {
          buttons.prop("disabled", false);

          var messages = $("<dl/>")
            .append($("<dt/>").text(translator.translate("Status")))
            .append($("<dd/>").text(result.status))
            ;

          if (result.errors) {
            messages.append($("<dt/>").text(translator.translate("Errors")));

            Object.keys(result.errors).forEach(function(lang) {
              messages.append($("<dt/>").text(lang));

              result.errors[lang].forEach(function(message) {
                messages.append($("<dd/>").text(message));
              });
            });
          }

          $("#status-messages").empty().append(messages);
        }, function(error) {
          buttons.prop("disabled", false);

          $("#status-messages").html(translator.translate('Synchronization failed due to an unknown error.'));
        });
      }, 1000);
    }
  };

  $.fn.spinner = function() {
    this.each(function(i, elem) {
      $(elem)
        .empty()
        .addClass("spinner")
        .append($("<div/>").addClass("double-bounce1"))
        .append($("<div/>").addClass("double-bounce2"))
    });
    return this;
  };

  $('[data-app="ptv-sync"]').on("submit", function(event) {
    event.preventDefault();
    run_ptv_sync(event.target);
  }).trigger("submit");
});

require(["$", "translator"], function($, translator) {
  $('[data-app="ptv-validate"]').each(function(i, elem) {
    var url = elem.dataset.url;

    $.get(url).then(function(response) {
      var label = response.valid
        ? translator.translate("No errors")
        : translator.translate("Document contains errors");

      var status = $("<dl/>")
        .append($("<dt/>").text(label));

      if (response.errors) {
        Object.keys(response.errors).forEach(function(lang) {
          status.append($("<dt/>").text(lang));
          response.errors[lang].forEach(function(error) {
            status.append($("<dd/>").text(error));
          });
        })
      }

      $(elem).empty().append(status);
    });
  });
});

require(["table.drag"], function($) {
  $("[data-sortable-list] table").tableDrag().on("tabledragsuccess", function(event, custom_data) {
    let url = window.location.pathname + "/tablesort";
    let handles = $(custom_data.rows).find(".drag-handle").toArray();

    $.post(url, {
      rows: handles.map(function(h) { return h.dataset.dragId }),
    });
  });
});
