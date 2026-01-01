-- Add 2FA fields to users table
ALTER TABLE users 
ADD COLUMN two_factor_secret VARCHAR(255) NULL,
ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0,
ADD COLUMN backup_codes TEXT NULL,
ADD COLUMN two_factor_verified_at TIMESTAMP NULL;

-- Update the table to ensure proper structure
ALTER TABLE users 
MODIFY COLUMN two_factor_secret VARCHAR(255) NULL,
MODIFY COLUMN two_factor_enabled TINYINT(1) DEFAULT 0,
MODIFY COLUMN backup_codes TEXT NULL,
MODIFY COLUMN two_factor_verified_at TIMESTAMP NULL;