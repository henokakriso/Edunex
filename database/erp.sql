-- EDUNEX ERP modules: schema + registry + demo data
SET NAMES utf8mb4;

-- 1. Modules registry --------------------------------------------------------
INSERT INTO modules (module_key, name, category, is_core) VALUES
('hr',          'HR Management',        'erp', 0),
('payroll',     'Payroll',              'erp', 0),
('recruitment', 'Recruitment',          'erp', 0),
('projects',    'Projects & Services',  'erp', 0),
('documents',   'Document Management',  'erp', 0),
('helpdesk',    'Help Desk',            'erp', 0),
('assets',      'Fixed Assets',         'erp', 0),
('fleet',       'Fleet Management',     'erp', 0)
ON DUPLICATE KEY UPDATE name = VALUES(name), category = 'erp';

-- 2. HR ----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS hr_positions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  department_id INT UNSIGNED NULL,
  level VARCHAR(40) NOT NULL DEFAULT 'staff',
  salary_scale DECIMAL(10,2) NOT NULL DEFAULT 0,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hr_staff (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  position_id INT UNSIGNED NULL,
  hire_date DATE NULL,
  employment_type ENUM('full','part','contract') NOT NULL DEFAULT 'full',
  supervisor_id INT UNSIGNED NULL,
  status ENUM('active','on_leave','terminated') NOT NULL DEFAULT 'active',
  is_demo TINYINT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_hr_staff (school_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hr_leave (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('annual','sick','maternity','study','unpaid') NOT NULL DEFAULT 'annual',
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  days INT UNSIGNED NOT NULL DEFAULT 1,
  reason VARCHAR(500) NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  decided_by INT UNSIGNED NULL,
  decided_at DATETIME NULL,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hr_attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  work_date DATE NOT NULL,
  check_in TIME NULL,
  check_out TIME NULL,
  status ENUM('present','absent','remote','late') NOT NULL DEFAULT 'present',
  is_demo TINYINT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_hr_att (user_id, work_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Payroll -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payroll_runs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  period VARCHAR(7) NOT NULL,
  status ENUM('draft','approved','paid') NOT NULL DEFAULT 'draft',
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_demo TINYINT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_payroll_period (school_id, period)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll_entries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  run_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  basic DECIMAL(10,2) NOT NULL DEFAULT 0,
  allowance DECIMAL(10,2) NOT NULL DEFAULT 0,
  deduction DECIMAL(10,2) NOT NULL DEFAULT 0,
  net DECIMAL(10,2) NOT NULL DEFAULT 0,
  bank VARCHAR(120) NULL,
  is_demo TINYINT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_pe (run_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Recruitment -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS job_openings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  department_id INT UNSIGNED NULL,
  job_type ENUM('full','part','contract') NOT NULL DEFAULT 'full',
  salary_range VARCHAR(80) NULL,
  description TEXT NULL,
  deadline DATE NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS job_applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  opening_id INT UNSIGNED NOT NULL,
  candidate_name VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(40) NULL,
  summary VARCHAR(500) NULL,
  stage ENUM('applied','screened','interview','offered','hired','rejected') NOT NULL DEFAULT 'applied',
  notes VARCHAR(500) NULL,
  decided_at DATETIME NULL,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Projects & Services -----------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  category ENUM('project','service','maintenance','event') NOT NULL DEFAULT 'project',
  description TEXT NULL,
  status ENUM('planning','active','completed','cancelled') NOT NULL DEFAULT 'planning',
  budget DECIMAL(12,2) NOT NULL DEFAULT 0,
  spent DECIMAL(12,2) NOT NULL DEFAULT 0,
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  start_date DATE NULL,
  end_date DATE NULL,
  manager_id INT UNSIGNED NULL,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS project_tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  assignee_id INT UNSIGNED NULL,
  due_date DATE NULL,
  priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  status ENUM('todo','in_progress','done') NOT NULL DEFAULT 'todo',
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Document Management -----------------------------------------------------
CREATE TABLE IF NOT EXISTS documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  category ENUM('policy','contract','report','certificate','administrative','other') NOT NULL DEFAULT 'other',
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(500) NULL,
  size INT UNSIGNED NOT NULL DEFAULT 0,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  uploaded_by INT UNSIGNED NULL,
  confidential TINYINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS document_versions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  document_id INT UNSIGNED NOT NULL,
  version INT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NULL,
  file_path VARCHAR(500) NULL,
  note VARCHAR(255) NULL,
  uploaded_by INT UNSIGNED NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Help Desk ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS helpdesk_tickets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  subject VARCHAR(200) NOT NULL,
  description TEXT NULL,
  category ENUM('it','maintenance','academic','administrative','other') NOT NULL DEFAULT 'other',
  priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  status ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  requester_id INT UNSIGNED NULL,
  assignee_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME NULL,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Fixed Assets ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS assets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  category ENUM('computers','furniture','vehicles','lab','office','building') NOT NULL DEFAULT 'office',
  asset_code VARCHAR(40) NOT NULL,
  purchase_date DATE NULL,
  purchase_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  useful_life_years INT UNSIGNED NOT NULL DEFAULT 5,
  asset_condition ENUM('new','good','fair','poor') NOT NULL DEFAULT 'good',
  status ENUM('in_use','under_maintenance','stored','retired') NOT NULL DEFAULT 'in_use',
  location VARCHAR(120) NULL,
  assigned_to INT UNSIGNED NULL,
  warranty_until DATE NULL,
  is_demo TINYINT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_asset_code (school_id, asset_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS asset_maintenance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_id INT UNSIGNED NOT NULL,
  work_date DATE NOT NULL,
  maint_type ENUM('routine','repair','warranty') NOT NULL DEFAULT 'routine',
  cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  note VARCHAR(255) NULL,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Fleet -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fleet_vehicles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  plate_number VARCHAR(20) NOT NULL,
  model VARCHAR(120) NULL,
  model_year INT UNSIGNED NULL,
  fuel_type ENUM('diesel','petrol','electric') NOT NULL DEFAULT 'diesel',
  capacity INT UNSIGNED NOT NULL DEFAULT 12,
  status ENUM('active','maintenance','out','retired') NOT NULL DEFAULT 'active',
  odometer_km INT UNSIGNED NOT NULL DEFAULT 0,
  insurance_until DATE NULL,
  is_demo TINYINT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_plate (school_id, plate_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fleet_trips (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  vehicle_id INT UNSIGNED NOT NULL,
  driver_id INT UNSIGNED NULL,
  trip_date DATE NOT NULL,
  purpose VARCHAR(255) NULL,
  origin VARCHAR(120) NULL,
  destination VARCHAR(120) NULL,
  start_km INT UNSIGNED NOT NULL DEFAULT 0,
  end_km INT UNSIGNED NOT NULL DEFAULT 0,
  fuel_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fleet_fuel (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL,
  refuel_date DATE NOT NULL,
  liters DECIMAL(8,2) NOT NULL DEFAULT 0,
  cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  odometer INT UNSIGNED NOT NULL DEFAULT 0,
  is_demo TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Demo seed data (Bahir Dar University = school 2) -----------------------
SET @s2 := (SELECT id FROM schools WHERE education_level = 'university' LIMIT 1);
SET @s1 := (SELECT id FROM schools ORDER BY id LIMIT 1);
SET @dir := (SELECT id FROM users WHERE email = 'director@edunex.local' LIMIT 1);
SET @dean := (SELECT id FROM users WHERE email = 'dean@edunex.local' LIMIT 1);
SET @reg := (SELECT id FROM users WHERE email = 'registrar@edunex.local' LIMIT 1);
SET @teach := (SELECT id FROM users WHERE email = 'marta@edunex.local' LIMIT 1);
SET @stu := (SELECT id FROM users WHERE email = 'student@edunex.local' LIMIT 1);
SET @dept := (SELECT id FROM departments WHERE school_id = @s2 ORDER BY id LIMIT 1);

-- HR
INSERT INTO hr_positions (school_id, title, department_id, level, salary_scale, is_demo) VALUES
(@s2, 'University Director', NULL, 'executive', 45000, 1),
(@s2, 'Dean of Faculty', NULL, 'executive', 35000, 1),
(@s2, 'Registrar', NULL, 'senior', 22000, 1),
(@s2, 'Senior Lecturer', @dept, 'senior', 25000, 1),
(@s2, 'ICT Officer', NULL, 'staff', 18000, 1),
(@s2, 'Lab Technician', @dept, 'staff', 15000, 1),
(@s2, 'School Bus Driver', NULL, 'staff', 8000, 1);
INSERT INTO hr_staff (school_id, user_id, position_id, hire_date, employment_type, supervisor_id, status, is_demo) VALUES
(@s2, @dir,  (SELECT id FROM hr_positions WHERE title='University Director' AND school_id=@s2 LIMIT 1), '2022-09-01', 'full', NULL, 'active', 1),
(@s2, @dean, (SELECT id FROM hr_positions WHERE title='Dean of Faculty' AND school_id=@s2 LIMIT 1),    '2021-10-15', 'full', @dir, 'active', 1),
(@s2, @reg,  (SELECT id FROM hr_positions WHERE title='Registrar' AND school_id=@s2 LIMIT 1),          '2020-11-02', 'full', @dir, 'active', 1),
(@s2, @teach,(SELECT id FROM hr_positions WHERE title='Senior Lecturer' AND school_id=@s2 LIMIT 1),    '2023-03-01', 'full', @dean, 'active', 1);
INSERT INTO hr_leave (school_id, user_id, type, start_date, end_date, days, reason, status, decided_by, decided_at, is_demo) VALUES
(@s2, @teach, 'annual',  '2026-08-10', '2026-08-14', 5, 'Family visit to Addis Ababa', 'approved', @dir, NOW() - INTERVAL 6 DAY, 1),
(@s2, @reg,   'sick',    '2026-07-28', '2026-07-30', 3, 'Flu', 'approved', @dir, NOW() - INTERVAL 3 DAY, 1),
(@s2, @dean,  'study',   '2026-09-01', '2026-09-30', 30, 'PhD coursework at AAU', 'pending', NULL, NULL, 1);
INSERT INTO hr_attendance (school_id, user_id, work_date, check_in, check_out, status, is_demo) VALUES
(@s2, @dir,   CURDATE() - INTERVAL 1 DAY, '08:12:00', '18:05:00', 'present', 1),
(@s2, @reg,   CURDATE() - INTERVAL 1 DAY, '08:30:00', '17:40:00', 'present', 1),
(@s2, @teach, CURDATE() - INTERVAL 1 DAY, '09:05:00', '16:20:00', 'late', 1),
(@s2, @teach, CURDATE() - INTERVAL 2 DAY, '08:00:00', '17:00:00', 'present', 1);

-- Payroll (draft + approved runs)
INSERT INTO payroll_runs (school_id, period, status, created_by, is_demo) VALUES
(@s2, '2026-06', 'paid', @dir, 1),
(@s2, '2026-07', 'draft', @dir, 1);
INSERT INTO payroll_entries (run_id, user_id, basic, allowance, deduction, net, bank, is_demo)
SELECT r.id, h.user_id, p.salary_scale, ROUND(p.salary_scale*0.15), ROUND(p.salary_scale*0.10),
       ROUND(p.salary_scale*1.05), 'CBE - BDU Branch', 1
FROM payroll_runs r
JOIN hr_staff h ON h.school_id = r.school_id AND h.status = 'active'
JOIN hr_positions p ON p.id = h.position_id
WHERE r.period = '2026-06';

-- Recruitment
INSERT INTO job_openings (school_id, title, department_id, job_type, salary_range, description, deadline, status, is_demo) VALUES
(@s2, 'Assistant Professor – Computer Science', @dept, 'full', '30,000 – 40,000 ETB', 'Teach undergraduate CS courses and supervise final year projects.', '2026-09-15', 'open', 1),
(@s2, 'University Librarian', NULL, 'full', '18,000 – 25,000 ETB', 'Manage the library, digital catalog and reading rooms.', '2026-08-30', 'open', 1),
(@s2, 'Campus Security Supervisor', NULL, 'contract', '10,000 – 12,000 ETB', 'Supervise the campus security team (12 guards).', '2026-07-20', 'closed', 1);
INSERT INTO job_applications (opening_id, candidate_name, email, phone, summary, stage, notes, decided_at, is_demo) VALUES
(1, 'Dr. Selam Bekele', 'selam.b@example.com', '+251911000111', 'PhD in CS from AAU, 6 years teaching experience.', 'screened', 'Strong research record.', NULL, 1),
(1, 'Mulugeta Tadesse', 'mulu.t@example.com', '+251922000222', 'MSc in Software Engineering, industry + teaching.', 'applied', NULL, NULL, 1),
(2, 'Hanna Girma', 'hanna.g@example.com', '+251933000333', 'MLIS from University of Pretoria.', 'interview', 'Excellent cataloging skills.', NULL, 1),
(3, 'Abebe Wolde', 'abebe.w@example.com', '+251944000444', '20 years security management experience.', 'hired', 'Hired July 2026.', '2026-07-12', 1);

-- Projects & Services
INSERT INTO projects (school_id, name, category, description, status, budget, spent, progress, start_date, end_date, manager_id, is_demo) VALUES
(@s2, 'New Science Laboratory Wing', 'project', 'Two-story lab building with 8 modern laboratories.', 'active', 15000000, 9200000, 65, '2026-01-10', '2027-03-30', @dean, 1),
(@s2, 'Campus Wi-Fi Upgrade', 'service', 'Replace campus Wi-Fi with Wi-Fi 6 mesh across 4 blocks.', 'active', 2400000, 1450000, 60, '2026-05-01', '2026-10-15', @dir, 1),
(@s2, 'Graduation Ceremony 2026', 'event', 'Annual graduation ceremony for 340 graduates.', 'planning', 850000, 120000, 15, '2026-09-01', '2026-10-30', @reg, 1),
(@s2, 'Library Roof Repair', 'maintenance', 'Fix leaks in the main library roof.', 'completed', 180000, 178000, 100, '2026-04-01', '2026-05-15', @dean, 1);
INSERT INTO project_tasks (project_id, title, assignee_id, due_date, priority, status, is_demo) VALUES
(1, 'Foundation & structural works', @dean, '2026-04-30', 'high', 'done', 1),
(1, 'Electrical and plumbing rough-in', @dean, '2026-08-15', 'high', 'in_progress', 1),
(1, 'Lab equipment procurement', @dir, '2026-12-01', 'medium', 'todo', 1),
(2, 'Survey existing AP locations', @dir, '2026-06-01', 'high', 'done', 1),
(2, 'Install mesh access points (Block A-B)', @dir, '2026-08-20', 'high', 'in_progress', 1),
(3, 'Book graduation venue', @reg, '2026-09-20', 'medium', 'todo', 1);

-- Documents
INSERT INTO documents (school_id, title, category, file_name, file_path, size, version, uploaded_by, confidential, is_demo) VALUES
(@s2, 'Staff Handbook 2026', 'policy', 'staff_handbook_2026.pdf', 'storage/erp/staff_handbook_2026.pdf', 1240000, 2, @dir, 0, 1),
(@s2, 'Annual Academic Report 2025', 'report', 'annual_report_2025.pdf', 'storage/erp/annual_report_2025.pdf', 3400000, 1, @reg, 0, 1),
(@s2, 'Payroll Policy v1', 'policy', 'payroll_policy_v1.pdf', 'storage/erp/payroll_policy_v1.pdf', 450000, 1, @dir, 1, 1),
(@s2, 'Supplier Contract – Lab Equipment', 'contract', 'lab_equipment_contract.pdf', 'storage/erp/lab_equipment_contract.pdf', 890000, 1, @dir, 1, 1);
INSERT INTO document_versions (document_id, version, file_name, file_path, note, uploaded_by, is_demo) VALUES
(1, 1, 'staff_handbook_2025.pdf', 'storage/erp/staff_handbook_2025.pdf', 'Original 2025 edition', @dir, 1),
(1, 2, 'staff_handbook_2026.pdf', 'storage/erp/staff_handbook_2026.pdf', 'Updated leave & payroll policy', @dir, 1);

-- Help Desk
INSERT INTO helpdesk_tickets (school_id, subject, description, category, priority, status, requester_id, assignee_id, created_at, resolved_at, is_demo) VALUES
(@s2, 'Projector not working in Hall B', 'The Epson projector in Hall B shows no signal from laptop.', 'it', 'high', 'in_progress', @teach, @dir, NOW() - INTERVAL 2 DAY, NULL, 1),
(@s2, 'Broken window in Lab 3', 'A window pane cracked during the storm last week.', 'maintenance', 'medium', 'open', @teach, NULL, NOW() - INTERVAL 1 DAY, NULL, 1),
(@s2, 'Transfer of transcripts', 'I need my official transcript sent to AAU for a master application.', 'academic', 'urgent', 'resolved', @stu, @reg, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 4 DAY, 1),
(@s2, 'New printer cartridge', 'HP printer in registrar office needs toner.', 'other', 'low', 'closed', @reg, @dir, NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 8 DAY, 1);
INSERT INTO ticket_comments (ticket_id, user_id, body, is_demo) VALUES
(1, @dir, 'Checked power and HDMI cable; suspect bulb. Ordered replacement.', 1),
(3, @reg, 'Transcript sent via DHL on 2026-08-03, tracking BA2551.', 1),
(4, @dir, 'Cartridge installed and printer tested.', 1);

-- Fixed Assets
INSERT INTO assets (school_id, name, category, asset_code, purchase_date, purchase_cost, useful_life_years, asset_condition, status, location, assigned_to, warranty_until, is_demo) VALUES
(@s2, 'Dell OptiPlex Lab Workstation x40', 'computers', 'BDU-CMP-0001', '2024-02-10', 45000, 5, 'good', 'in_use', 'Lab 1', @teach, '2027-02-10', 1),
(@s2, 'Epson EB-2250 Projector', 'office', 'BDU-PRJ-0002', '2023-06-15', 32000, 5, 'fair', 'under_maintenance', 'Hall B', NULL, '2026-06-15', 1),
(@s2, 'Lecture Hall Chairs (set of 60)', 'furniture', 'BDU-FUR-0003', '2022-09-01', 240000, 10, 'good', 'in_use', 'Hall A', NULL, NULL, 1),
(@s2, 'Toyota Coaster School Bus', 'vehicles', 'BDU-VEH-0004', '2021-05-20', 3800000, 12, 'good', 'in_use', 'Parking', NULL, '2027-05-20', 1),
(@s2, 'Spectrophotometer UV-Vis', 'lab', 'BDU-LAB-0005', '2025-01-12', 780000, 8, 'new', 'in_use', 'Chem Lab', @teach, '2030-01-12', 1);
INSERT INTO asset_maintenance (asset_id, work_date, maint_type, cost, note, is_demo) VALUES
(2, '2026-08-04', 'repair', 4800, 'Bulb replacement quote approved.', 1),
(1, '2026-06-20', 'routine', 900, 'Cleaning and RAM check.', 1);

-- Fleet
INSERT INTO fleet_vehicles (school_id, name, plate_number, model, model_year, fuel_type, capacity, status, odometer_km, insurance_until, is_demo) VALUES
(@s2, 'BDU Bus 1', '3-BDU-001', 'Toyota Coaster', 2021, 'diesel', 28, 'active', 48250, '2027-05-20', 1),
(@s2, 'Campus Pickup', '3-BDU-007', 'Isuzu D-Max', 2022, 'diesel', 5, 'active', 26100, '2026-12-10', 1),
(@s2, 'Minibus (Field trips)', '3-BDU-012', 'Hyundai H-1', 2019, 'diesel', 12, 'maintenance', 59300, '2026-11-01', 1);
INSERT INTO fleet_trips (school_id, vehicle_id, driver_id, trip_date, purpose, origin, destination, start_km, end_km, fuel_cost, is_demo) VALUES
(@s2, 1, NULL, '2026-08-03', 'Student pick-up from bus station', 'Bahir Dar Station', 'Campus', 48000, 48120, 850, 1),
(@s2, 1, NULL, '2026-08-05', 'Faculty field trip to Lake Tana', 'Campus', 'Tana Shore', 48150, 48410, 1450, 1),
(@s2, 2, NULL, '2026-08-04', 'Office supplies run', 'Campus', 'City center', 26000, 26090, 620, 1);
INSERT INTO fleet_fuel (vehicle_id, refuel_date, liters, cost, odometer, is_demo) VALUES
(1, '2026-08-03', 40, 3800, 48000, 1),
(1, '2026-08-05', 45, 4275, 48150, 1),
(2, '2026-08-04', 25, 2375, 26000, 1);
