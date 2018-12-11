CREATE TABLE organisations_accessibility(
    organisation_id INT NOT NULL,
    accessibility_id INT NOT NULL,
    PRIMARY KEY(organisation_id, accessibility_id),
    FOREIGN KEY (organisation_id)
        REFERENCES organisations(id)
        ON DELETE CASCADE,
    FOREIGN KEY (accessibility_id)
        REFERENCES accessibility(id)
        ON DELETE CASCADE
);

CREATE TABLE template_references(
    id SERIAL,
    entity_type TEXT NOT NULL,
    created timestamp with time zone NOT NULL,
    modified timestamp without time zone DEFAULT now() NOT NULL,
    overrides jsonb,
    translations jsonb,

    group_id INT NOT NULL,
    organisation_id INT NOT NULL,

    -- Used with Service entities
    service_id INT,

    -- Used with AccessibilityFeature entities
    accessibility_id INT,

    FOREIGN KEY(group_id)
        REFERENCES roles(id),

    FOREIGN KEY(organisation_id)
        REFERENCES organisations(id)
        ON DELETE CASCADE,

    FOREIGN KEY(service_id)
        REFERENCES services(id),

    FOREIGN KEY(accessibility_id)
        REFERENCES accessibility(id)
);

ALTER TABLE cities ADD COLUMN library_name VARCHAR(200);
ALTER TABLE regions DROP COLUMN library_name;

ALTER TABLE organisations ADD COLUMN web_library VARCHAR(255);



ALTER TABLE organisations ADD COLUMN helmet_sierra_id VARCHAR(40);
