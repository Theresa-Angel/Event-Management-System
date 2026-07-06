-- Migration script for Registration Module

-- Update registrations table to include status, ticket_id and qr_code
ALTER TABLE registrations 
ADD COLUMN IF NOT EXISTS status ENUM('confirmed', 'waitlisted', 'cancelled') DEFAULT 'confirmed',
ADD COLUMN IF NOT EXISTS ticket_id VARCHAR(20) UNIQUE,
ADD COLUMN IF NOT EXISTS qr_code TEXT,
ADD COLUMN IF NOT EXISTS registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Optional: If you want to migrate existing data
UPDATE registrations SET status = 'confirmed' WHERE status IS NULL;
UPDATE registrations SET registration_date = NOW() WHERE registration_date IS NULL;
