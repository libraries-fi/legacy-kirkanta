<?php

return [
    'resolver_configs' => [
        'collections' => [
            'dev/bootstrap.css' => [
                'lib/bootstrap/dist/css/bootstrap.min.css',
                'lib/bootstrap/dist/css/bootstrap-theme.min.css',
            ],
            'dev/theme.less' => [
                'lib/bootstrap/less/variables.less',
                'less/layout.less',
                'less/navigation.less',
                'less/form.less',
                'less/periods.less',
                'less/login.less',
                'less/schedules-preview.less',
                'less/form-collection.less',
                'less/custom-data.less',
                'less/tools.less',
                'less/spinner.less',

                'js/kirkanta-tools/less/slider.less',
                'js/samufw-inputs/less/select.less',
                'js/samufw-inputs/less/calendar.less',
                'js/samufw-inputs/less/datepicker.less',
            ],
            'dev/libs.js' => [
                'lib/requirejs/require.js',
                'js/config.require.js',
            ],
            'dev/script.js' => [
                'js/strictmode.js',
                'js/init.js',
                'js/organisations.js',
                'js/periods.js',
                'js/templates.js',
                'js/weblinks.js',

                // From ServiceTool module
                'js/service-tool.js',
            ],
            'dev/less.js' => [
                'lib/less/dist/less.min.js',
            ],
            'bundle/style.css' => [
                'build/css/style.css',
            ],
        ],
        'paths' => [
            __DIR__ . '/../public',
            __DIR__ . '/../../ServiceTool/public'
        ],
    ]
];
