<?php
/**
 * This tool calculates a quality scoring for Service and ServiceType entities. This rating can then
 * be used when merging duplicate ServiceType entries together and for selecting the best
 * transations to preserve.
 */

function tr_score(stdClass $item) {
  $score = 0;
  $use_fields = ['name', 'short_description', 'description'];

  foreach ($item->translations as $lang => $trdata) {
    $has_lang = false;

    if ($lang == 'ru' && !empty($trdata->name) && $trdata->name == $item->name) {
      continue;
    }
    foreach ($trdata as $field => $value) {
      if (in_array($field, $use_fields) && !empty($value)) {
        $has_lang = true;
        $score += 10;
      }
    }
    if ($has_lang) {
      $score += 50;
    }
  }

  return $score;
}

$db = new PDO('pgsql:dbname=kirkanta', null, null, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

$result = $db->query("
  SELECT id, name, translations
  FROM services_new
  ORDER BY name
");

$db->beginTransaction();
$query = $db->prepare('UPDATE services_new SET tr_score = ? WHERE id = ?');

foreach ($result as $row) {
  $row->translations = json_decode($row->translations);
  $row->tr_score = tr_score($row);

  $query->execute([$row->tr_score, $row->id]);
}

$result = $db->query("
  SELECT id, name, translations
  FROM service_types
  ORDER BY name
");

$query = $db->prepare('UPDATE service_types SET tr_score = ? WHERE id = ?');

foreach ($result as $row) {
  $row->translations = json_decode($row->translations);
  $row->tr_score = tr_score($row);

  $query->execute([$row->tr_score, $row->id]);
}

$db->commit();
