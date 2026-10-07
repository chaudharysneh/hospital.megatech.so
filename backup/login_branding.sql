-- Apply to each hospital database before deploying login branding.
ALTER TABLE sch_settings
 ADD COLUMN login_logo VARCHAR(255) NOT NULL DEFAULT '',
 ADD COLUMN login_banner VARCHAR(255) NOT NULL DEFAULT '';
