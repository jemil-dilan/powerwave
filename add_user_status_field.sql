-- Add status field to users table for user deactivation functionality
-- Run this SQL command to add the status field to your existing users table

ALTER TABLE users 
ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' 
AFTER role;

-- Update any existing users to be active by default
UPDATE users SET status = 'active' WHERE status IS NULL;

-- Add an index for better performance when filtering by status
ALTER TABLE users ADD INDEX idx_user_status (status);
