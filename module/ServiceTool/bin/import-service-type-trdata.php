<?php
/**
 * This tool imports translations from Service entities into their parent ServiceType entities.
 * Used Service entity is selected by its 'tr_score' quality rating.
 */

function merge_trdata(stdClass $type, stdClass $service) {
  $use_fields = [
    'name' => 'name',
  ];
  $translations = $type->translations;
  foreach ($service->translations as $lang => $trdata) {
    $translations->{$lang}->description = '';
    foreach ($trdata as $field => $value) {
      if (isset($use_fields[$field])) {
        $mapped = $use_fields[$field];
        if (!empty($value) && $value != $type->{$mapped}) {
          $translations->{$lang}->{$mapped} = $value;
        }
      }
    }
  }
}

$db = new PDO('pgsql:dbname=kirkanta', null, null, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

$db->beginTransaction();

$types = $db->query('SELECT id, name, description, translations FROM service_types ORDER BY name');
$select = $db->prepare('SELECT name, short_description, translations FROM services_new WHERE name = ?');
$update = $db->prepare('UPDATE service_types SET translations = ? WHERE id = ?');

foreach ($types as $type) {
  $type->translations = json_decode($type->translations);
  $select->execute([$type->name]);

  foreach ($select as $service) {
    $service->translations = json_decode($service->translations);
    merge_trdata($type, $service);
    $update->execute([json_encode($type->translations), $type->id]);
  }
}

$db->commit();
