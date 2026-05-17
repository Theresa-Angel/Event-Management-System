-- Database Optimization: Add Missing Indexes
-- Run this to improve query performance

-- Events table indexes
ALTER TABLE events ADD INDEX idx_status (status);
ALTER TABLE events ADD INDEX idx_start_date (start_date);
ALTER TABLE events ADD INDEX idx_organizer_id (organizer_id);
ALTER TABLE events ADD INDEX idx_category (category);
ALTER TABLE events ADD INDEX idx_status_start_date (status, start_date);

-- Registrations table indexes
ALTER TABLE registrations ADD INDEX idx_event_id (event_id);
ALTER TABLE registrations ADD INDEX idx_user_id (user_id);
ALTER TABLE registrations ADD INDEX idx_status (status);
ALTER TABLE registrations ADD INDEX idx_registration_date (registration_date);
ALTER TABLE registrations ADD INDEX idx_event_status (event_id, status);

-- Users table indexes
ALTER TABLE users ADD INDEX idx_role (role);
ALTER TABLE users ADD INDEX idx_status (status);
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE users ADD INDEX idx_role_status (role, status);

-- Notifications table indexes
ALTER TABLE notifications ADD INDEX idx_user_id (user_id);
ALTER TABLE notifications ADD INDEX idx_created_at (created_at);
ALTER TABLE notifications ADD INDEX idx_user_created (user_id, created_at);

-- Feedback table indexes
ALTER TABLE feedback ADD INDEX idx_status (status);
ALTER TABLE feedback ADD INDEX idx_submission_date (submission_date);
