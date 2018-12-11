
CREATE TABLE ptv_meta (
  entity_id int NOT NULL,
  entity_type varchar(20) NOT NULL,
  ptv_identifier uuid,
  last_sync timestamp,
  method smallint NOT NULL DEFAULT 0,
  enabled boolean NOT NULL DEFAULT false,
  published boolean NOT NULL DEFAULT false,
  last_log TEXT,

  PRIMARY KEY(entity_id, entity_type),
  UNIQUE(ptv_identifier)
);
