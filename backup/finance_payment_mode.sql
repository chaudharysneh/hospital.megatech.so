-- Existing records have an unknown payment mode; select it when editing.
ALTER TABLE income ADD COLUMN payment_mode VARCHAR(50) NOT NULL DEFAULT '';
ALTER TABLE expenses ADD COLUMN payment_mode VARCHAR(50) NOT NULL DEFAULT '';
