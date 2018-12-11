<?php

/**
 * Image files are stored in these places:
 * consortiums.logo
 * persons.picture
 * pictures.filename
 * services_new.picture
 */

$db = new PDO('pgsql:dbname=library_directory');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$schema = [
    ['table' => 'consortiums', 'field' => 'logo'],
    ['table' => 'persons', 'field' => 'picture'],
    ['table' => 'pictures', 'field' => 'filename'],
    ['table' => 'services_new', 'field' => 'picture'],
];

$db->beginTransaction();
foreach ($schema as $s) {
    $smt = $db->prepare(sprintf('UPDATE %s SET %s=? WHERE id=?', $s['table'], $s['field']));
    $result = $db->query(sprintf('SELECT id, %s file FROM %s', $s['field'], $s['table']));

    foreach ($result as $row) {
        $filename = pathinfo($row['file'], PATHINFO_FILENAME) . '.jpeg';
        $smt->execute([$filename, $row['id']]);
    }
}
$db->commit();
