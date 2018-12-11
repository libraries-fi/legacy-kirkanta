
requirejs.config({
  config: {
    i18n: {
      locale: document.documentElement.lang,
    }
  },
  baseUrl: "/js",
  paths: {
    handlebars: "../lib/handlebars/handlebars",
    moment: "../lib/moment/min/moment-with-locales.min",
    underscore: "../lib/underscore/underscore-min",
    i18n: "../lib/requirejs-i18n/i18n",
    text: "../lib/requirejs-text/text",

    // Not sure if needed anymore
    // "$": "../lib/zepto/zepto",

    "ckeditor": "../lib/ckeditor/ckeditor",

    "$": "../lib/jquery/dist/jquery.min",
  },
  shim: {
    "samufw/view": ["handlebars"],
    "ckeditor": {
      exports: "CKEDITOR",
    },
    "$": {
      exports: "$",
    }
  },
  packages: [
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
  // config: {
  //   i18n: {
  //     locale: "fi-fi",
  //   }
  // }
});

less = {
  end: "development",
};
