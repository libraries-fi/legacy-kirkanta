CREATE TYPE web_link_entity AS ENUM('consortium', 'organisation');

CREATE TABLE web_link_groups (
  id serial NOT NULL,
  entity web_link_entity NOT NULL,
  group_id int,

  organisation_id int,
  consortium_id int,

  name varchar(100) NOT NULL,
  identifier varchar(40) NOT NULL,
  description TEXT,
  translations jsonb,

  PRIMARY KEY(id),
  FOREIGN KEY (group_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
  FOREIGN KEY (consortium_id) REFERENCES consortiums(id) ON DELETE CASCADE,
  UNIQUE (consortium_id, identifier),
  UNIQUE (organisation_id, identifier)
);

CREATE TABLE web_links (
  id serial NOT NULL,
  link_group_id int NOT NULL,
  entity web_link_entity NOT NULL,
  name varchar(100) NOT NULL,
  url text NOT NULL,
  description text,
  translations jsonb,
  organisation_id int,
  consortium_id int,

  PRIMARY KEY(id),
  FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
  FOREIGN KEY (consortium_id) REFERENCES consortiums(id) ON DELETE CASCADE,
  FOREIGN KEY (link_group_id) REFERENCES web_link_groups(id)
);

INSERT INTO web_link_groups (entity, identifier, name, translations, organisation_id, group_id)
  SELECT 'organisation', 'default', 'Linkit', '{"en": {"name": "Links"}, "sv": {"name": "Länkar"}}', id, group_id
  FROM organisations;

INSERT INTO web_links (entity, link_group_id, name, url, description, translations, organisation_id)
  SELECT 'organisation', b.id, a.name, a.url, a.description, a.translations, a.organisation_id
  FROM external_links a INNER JOIN web_link_groups b ON a.organisation_id = b.organisation_id;







ALTER TABLE finna_consortium_data ADD COLUMN finna_coverage int;
ALTER TABLE finna_consortium_data ADD COLUMN service_point_id int;

ALTER TABLE finna_consortium_data ADD FOREIGN KEY (service_point_id) REFERENCES organisations(id) ON DELETE SET NULL;



ALTER TABLE consortiums ADD COLUMN created timestamp with time zone;
ALTER TABLE consortiums ADD COLUMN modified timestamp NOT NULL DEFAULT NOW();
ALTER TABLE consortiums ADD COLUMN group_id int;
ALTER TABLE consortiums ADD FOREIGN KEY (group_id) REFERENCES roles(id);

ALTER TABLE consortiums ADD COLUMN state smallint NOT NULL DEFAULT 0;
UPDATE consortiums SET state = 1;


-- Temporary column for scoring the quality of contained translations
ALTER TABLE services_new ADD COLUMN tr_score int NOT NULL DEFAULT 0;
ALTER TABLE service_types ADD COLUMN tr_score int NOT NULL DEFAULT 0;

ALTER TABLE organisations ADD COLUMN cached_legacy_times jsonb;














ALTER TABLE addresses ADD column info varchar(120);



ALTER TABLE phone_numbers ADD COLUMN weight int NOT NULL DEFAULT 0;
ALTER TABLE web_links ADD COLUMN weight int NOT NULL DEFAULT 0;
