
// node ../../node_modules/requirejs/bin/r.js -o build.js

({
    "name": "init",
    "include": [
      "config.require",
      "table.drag",
      "organisations",
      "periods",
      "templates",
      "../../../ServiceTool/public/js/service-tool",

      "../lib/requirejs/require.js",
      "../lib/jquery/dist/jquery.min.js",
      "../lib/bootstrap/dist/js/bootstrap.min.js",
      "ckeditor-custom-config",
      "ckeditor-config",
      "ckeditor-styles",
      "ckeditor-lang-" + config.locale.substring(0, 2),
    ],
    "shim": {
      "ckeditor": {
        "exports": "CKEDITOR",
      },
      "$": {
        "exports": "$",
      }
    },
  "paths": {
    "handlebars": "../lib/handlebars/handlebars",
    "moment": "../lib/moment/min/moment-with-locales.min",
    "underscore": "../lib/underscore/underscore-min",
    "i18n": "../lib/requirejs-i18n/i18n",
    "text": "../lib/requirejs-text/text",

    "ckeditor": "../lib/ckeditor/ckeditor",
    "ckeditor-config": "../lib/ckeditor/config",
    "ckeditor-custom-config": "config.ckeditor",
    "ckeditor-lang-en": "../lib/ckeditor/lang/en",
    "ckeditor-lang-fi": "../lib/ckeditor/lang/fi",
    "ckeditor-lang-sv": "../lib/ckeditor/lang/sv",
    "ckeditor-styles": "../lib/ckeditor/styles",

    "$": "../lib/jquery/dist/jquery.min",
  },
  "packages": [
    {
      "name": "samufw-inputs",
      "location": "samufw-inputs/src"
    },
    {
      "name": "samufw",
      "location": "samufw/src"
    },
    {
      "name": "kirkanta-tools",
      "location": "kirkanta-tools/src"
    }
  ],
})
