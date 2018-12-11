<?php

$basedir = __DIR__;

passthru('rm -rf ' . $basedir . '/../../public/dist/*');

chdir($basedir);

passthru('mkdir ' . $basedir . '/../../public/dist/bootstrap');
passthru('cp public/lib/bootstrap/dist/css/bootstrap.min.css ' . $basedir . '/../../public/dist/bootstrap/bootstrap.css');
passthru('cp public/lib/bootstrap/dist/css/bootstrap-theme.min.css ' . $basedir . '/../../public/dist/bootstrap/bootstrap-theme.css');
passthru('cp -r public/lib/bootstrap/dist/fonts ' . $basedir . '/../../public/dist/fonts');

passthru('mkdir ' . $basedir . '/../../public/dist/ckeditor');
passthru('cp -r public/lib/ckeditor/skins ' . $basedir . '/../../public/dist/ckeditor');
passthru('cp -r public/lib/ckeditor/contents.css ' . $basedir . '/../../public/dist/ckeditor');

passthru('node node_modules/less/bin/lessc public/less/build.less > ' . $basedir . '/../../public/dist/style.css');

chdir($basedir . '/public/js');
passthru('../../node_modules/requirejs/bin/r.js -o build.js locale=fi-fi out=../../../../public/dist/script-fi.js');
passthru('../../node_modules/requirejs/bin/r.js -o build.js locale=en-fi out=../../../../public/dist/script-en.js');
passthru('../../node_modules/requirejs/bin/r.js -o build.js locale=sv-fi out=../../../../public/dist/script-sv.js');

passthru('rm -rf ' . $basedir . '/public/build');
