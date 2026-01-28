CREATE DATABASE municipality_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE municipality_db;

CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE department_translations (
  department_id INT NOT NULL,
  lang ENUM('ge','en') NOT NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT NULL,
  address VARCHAR(255) NULL,
  phone VARCHAR(50) NULL,
  email VARCHAR(120) NULL,
  PRIMARY KEY (department_id, lang),
  CONSTRAINT fk_dept_tr_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('SUPER_ADMIN','DEPT_ADMIN') NOT NULL,
  department_id INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NULL,
  slug VARCHAR(120) NOT NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_pages_slug (slug),
  INDEX idx_pages_dept (department_id),
  CONSTRAINT fk_pages_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE page_translations (
  page_id INT NOT NULL,
  lang ENUM('ge','en') NOT NULL,
  title VARCHAR(255) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  PRIMARY KEY (page_id, lang),
  CONSTRAINT fk_page_tr_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE news (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NULL,
  cover VARCHAR(255) NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'published',
  published_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_news_pub (published_at),
  INDEX idx_news_dept (department_id),
  CONSTRAINT fk_news_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE news_translations (
  news_id INT NOT NULL,
  lang ENUM('ge','en') NOT NULL,
  title VARCHAR(255) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  PRIMARY KEY (news_id, lang),
  CONSTRAINT fk_news_tr_news FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO departments (slug) VALUES
('mayor-office'),
('finance'),
('infrastructure'),
('social-affairs'),
('culture'),
('education'),
('health'),
('legal'),
('hr'),
('it'),
('tourism'),
('public-relations');

INSERT INTO department_translations (department_id, lang, name, description, address, phone, email)
SELECT d.id, 'ge',
  CASE d.slug
    WHEN 'mayor-office' THEN 'მერის აპარატი'
    WHEN 'finance' THEN 'ფინანსური სამსახური'
    WHEN 'infrastructure' THEN 'ინფრასტრუქტურის სამსახური'
    WHEN 'social-affairs' THEN 'სოციალური სამსახური'
    WHEN 'culture' THEN 'კულტურის სამსახური'
    WHEN 'education' THEN 'განათლების სამსახური'
    WHEN 'health' THEN 'ჯანდაცვის სამსახური'
    WHEN 'legal' THEN 'იურიდიული სამსახური'
    WHEN 'hr' THEN 'ადამიანური რესურსები'
    WHEN 'it' THEN 'IT სამსახური'
    WHEN 'tourism' THEN 'ტურიზმის სამსახური'
    WHEN 'public-relations' THEN 'საზოგადოებასთან ურთიერთობა'
  END,
  'აღწერა მოგვიანებით შეგიძლიათ დაამატოთ ადმინისტრატორიდან.',
  'მისამართი',
  '+995 5xx xx xx xx',
  'info@municipality.ge'
FROM departments d;

INSERT INTO department_translations (department_id, lang, name, description, address, phone, email)
SELECT d.id, 'en',
  CASE d.slug
    WHEN 'mayor-office' THEN 'Mayor Office'
    WHEN 'finance' THEN 'Finance Department'
    WHEN 'infrastructure' THEN 'Infrastructure Department'
    WHEN 'social-affairs' THEN 'Social Affairs'
    WHEN 'culture' THEN 'Culture Department'
    WHEN 'education' THEN 'Education Department'
    WHEN 'health' THEN 'Health Department'
    WHEN 'legal' THEN 'Legal Department'
    WHEN 'hr' THEN 'Human Resources'
    WHEN 'it' THEN 'IT Department'
    WHEN 'tourism' THEN 'Tourism Department'
    WHEN 'public-relations' THEN 'Public Relations'
  END,
  'You can update this description from the admin panel.',
  'Address',
  '+995 5xx xx xx xx',
  'info@municipality.ge'
FROM departments d;

INSERT INTO pages (department_id, slug, status) VALUES (NULL, 'about', 'published');
SET @pid = LAST_INSERT_ID();
INSERT INTO page_translations (page_id, lang, title, body) VALUES
(@pid, 'ge', 'ჩვენს შესახებ', '<p>ეს არის მუნიციპალიტეტის საინფორმაციო ვებგვერდი.</p>'),
(@pid, 'en', 'About', '<p>This is the municipality informational website.</p>');

-- Create SUPER ADMIN after generating password hash:
-- INSERT INTO users (email, password_hash, role, department_id)
-- VALUES ('admin@municipality.ge', 'PASTE_HASH_HERE', 'SUPER_ADMIN', NULL);
