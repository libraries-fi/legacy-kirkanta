<?php

/**
 * Image files are stored in these places:
 * consortiums.logo
 * persons.picture
 * pictures.filename
 * services_new.picture
 */

$fileroot = '../../public/files/images';
$cache = [];

foreach (glob($fileroot . '/original/*.*') as $path) {
  $name = pathinfo($path, PATHINFO_FILENAME);
  $cache[$name] = $path;
  // $name = basename($path);
  // $cache[$name] = basename($name);
}

$total = count($cache);

$db = new PDO('pgsql:dbname=library_directory');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$result = $db->query('
  SELECT logo file FROM consortiums UNION
  SELECT picture file FROM persons UNION
  SELECT filename file FROM pictures UNION
  SELECT picture file FROM services_new
');

$rows = 0;
foreach ($result as $row) {
  $rows++;
  $name = pathinfo($row['file'], PATHINFO_FILENAME);
  unset($cache[$name]);
}

$has_scaled = 0;
foreach ($cache as $path) {
    $sizes = ['small', 'medium', 'large', 'huge'];
#    $s_path = sprintf('%s/small/%s', $fileroot, basename($path));
#    $s_path_alt = sprintf('%s/small/%s.jpeg', $fileroot, pathinfo($path, PATHINFO_FILENAME));
#    $has_scaled += file_exists($s_path);
#    $has_scaled += file_exists($s_path_alt);

#    continue;

    rename($path, sprintf('%s/trash/original/%s', $fileroot, basename($path)));

    foreach ($sizes as $size) {
        $s_path = sprintf('%s/%s/%s', $fileroot, $size, basename($path));
        $s_path_alt = sprintf('%s/%s/%s.jpeg', $fileroot, $size, pathinfo($path, PATHINFO_FILENAME));

        @rename($s_path, sprintf('%s/trash/%s/%s', $fileroot, $size, basename($path)));
        @rename($s_path_alt, sprintf('%s/trash/%s/%s.jpeg', $fileroot, $size, pathinfo($path, PATHINFO_FILENAME)));
    }
}


#var_dump($has_scaled, count($cache));
#printf("Found %d/%d (%d files in DB) junk files.\n", count($cache), $total, $rows);
