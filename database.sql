-- Database Schema for Apollo Support Service Website Modernization

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admins
-- ----------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a default admin (username: admin, password: password123, hashed using password_hash)
INSERT INTO `admins` (`username`, `password`, `email`) 
VALUES ('admin', '$2y$10$tM7o04V9c4g5vj6jGf2Ue.G0rNExL/v9.aHwS0Wp2n3Gg8o8G7jR6', 'admin@apolloservices.com')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- ----------------------------
-- Table structure for services
-- ----------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_id` VARCHAR(50) NOT NULL UNIQUE,
  `label` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `accent` VARCHAR(30) DEFAULT 'pink',
  `description` TEXT,
  `base_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default services matching the app definition
INSERT INTO `services` (`service_id`, `label`, `category`, `accent`, `description`, `base_price`) VALUES
('office', 'Office Cleaning', 'commercial', 'pink', 'Professional cleaning that creates hygienic, productive workspaces with minimal business disruption.', 150.00),
('carpet', 'Carpet Cleaning', 'residential', 'lavender', 'Deep cleaning that removes 98% of dirt and allergens from carpets and upholstery, with quick drying.', 120.00),
('window', 'Window Cleaning', 'exterior', 'ochre', 'Streak-free interior and exterior window cleaning with professional tools for crystal-clear views.', 80.00),
('lawn', 'Cleaning & Lawn Mowing', 'exterior', 'pink', 'Complete property maintenance combining interior cleaning with exterior lawn care.', 140.00),
('outings', 'Outings - Day & Night', 'ndis', 'teal', 'Safe, enjoyable social outings with companion support for day trips or evening activities. NDIS approved.', 60.00),
('transport', 'Transport Services', 'ndis', 'lavender', 'Safe, reliable transport for medical appointments, shopping and social outings with trained assistants.', 45.00),
('endlease', 'End of Lease Cleaning', 'residential', 'peach', 'Bond-back guaranteed cleaning that meets all property-manager standards for stress-free moving.', 280.00),
('personal', 'Personal Care', 'ndis', 'ochre', 'Compassionate in-home support for daily living to help maintain independence and dignity. NDIS approved.', 55.00),
('pressure', 'Pressure Cleaning', 'exterior', 'mint', 'High-powered exterior cleaning that removes grime, mould and dirt to restore surfaces.', 110.00),
('facility', 'Facility Management', 'commercial', 'coral', 'Comprehensive facility solutions to ensure smooth operations, safety and cleanliness of your property.', 350.00)
ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `base_price`=VALUES(`base_price`);

-- ----------------------------
-- Table structure for bookings
-- ----------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `service_type` VARCHAR(50) NOT NULL,
  `booking_date` DATE NOT NULL,
  `booking_time` VARCHAR(20) NOT NULL,
  `notes` TEXT,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending, confirmed, cancelled
  `total_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for invoices
-- ----------------------------
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `booking_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `status` VARCHAR(20) NOT NULL DEFAULT 'unpaid', -- unpaid, paid
  `due_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for payments
-- ----------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `transaction_id` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `status` VARCHAR(20) NOT NULL DEFAULT 'completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for blog_posts
-- ----------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `summary` TEXT,
  `content` MEDIUMTEXT NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'published', -- draft, published
  `published_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a couple of sample blog posts
INSERT INTO `blog_posts` (`title`, `slug`, `summary`, `content`, `category`) VALUES
('The Importance of Green Cleaning for Commercial Spaces', 'green-cleaning-commercial-spaces', 'Why eco-friendly commercial cleaning makes a difference for health and productivity in Albury-Wodonga.', '<p>Using green, eco-friendly cleaning products in commercial workspaces is no longer just a trend—it is a critical necessity. In offices and commercial spaces, high-density environments are prone to accumulating allergens, dust, and residues from harsh chemical cleaners.</p><h2>Healthier Work Environments</h2><p>Standard industrial chemical cleaners often contain volatile organic compounds (VOCs) that can cause headaches, fatigue, and respiratory irritation. Switching to plant-based, biodegradable cleaners keeps the air quality pure, helping employees feel better and work more productively.</p><h2>Sustainable and Biodegradable</h2><p>Traditional cleaners also leave residues that can seep into waterways. Our eco-friendly solutions break down naturally without harming ecosystems, matching the Albury-Wodonga community\'s commitment to protecting our natural local rivers and land.</p>', 'Commercial'),
('End of Lease Cleaning: A Step-by-Step Guide', 'end-of-lease-cleaning-guide', 'A complete walkthrough of what property managers expect to guarantee your full bond refund.', '<p>Moving out is stressful enough without worrying about getting your security deposit back. A thorough end-of-lease clean is the key to leaving property managers impressed and ensuring a smooth transition.</p><h2>The Critical Areas</h2><ul><li><strong>The Kitchen Oven:</strong> Property managers always check this. Grease and baked-on carbon must be completely removed.</li><li><strong>Tiles and Grout:</strong> Bathroom walls, floors, and showers need scrubbing to eliminate mold and water scale.</li><li><strong>Window Tracks:</strong> Dust and insects collect in window tracks. Vacuuming and wiping them clean is essential.</li></ul><h2>Why Choose Professional Assistance?</h2><p>Property managers use standard checklists to inspect homes. Apollo\'s End of Lease Cleaning service is specifically aligned with these checklists and comes with a 100% bond-back guarantee, letting you focus entirely on your new move.</p>', 'Residential')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `content`=VALUES(`content`);

-- ----------------------------
-- Table structure for inquiries
-- ----------------------------
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `message` TEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'unread', -- unread, read, replied
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
