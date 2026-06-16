-- ============================================================
-- Job Search Portal: Role Genie 
-- ICS499-50 Capstone Sovann Phay & O'Shae Berteaux 
-- 5 Tables: users, user_profiles, resumes, job_listings, applications
-- ============================================================

CREATE DATABASE IF NOT EXISTS role_genie
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE role_genie;

-- ============================================================
--  1. USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    email         VARCHAR(255)    NOT NULL,
    password_hash VARCHAR(255)    NOT NULL, -- php use $hash = password_hash($_POST['password'], PASSWORD_BCRYPT); when user registers
    full_name     VARCHAR(255)        NULL,
    phone         VARCHAR(30)         NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
    primary key (id)
);

-- ============================================================
--  2. USER PROFILES
--  Stores only users job website URL.
-- ============================================================
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    linkedin_url VARCHAR(512) NULL,
    indeed_url VARCHAR(512) NULL,
    glassdoor_url VARCHAR(512) NULL,
    monster_url VARCHAR(512) NULL,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
    primary key (id),
	foreign key (user_id) references users(id)
        on delete cascade
		on update cascade
);

-- ============================================================
--  3. RESUMES
-- ============================================================
CREATE TABLE IF NOT EXISTS resumes (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED     NOT NULL,
    filename    VARCHAR(255)     NOT NULL,
    file_path   VARCHAR(512)     NOT NULL,
    file_type   ENUM('pdf') NOT NULL,
    parsed_text LONGTEXT             NULL,
    is_active   TINYINT(1)       NOT NULL DEFAULT 1,
    uploaded_at DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    primary  key (id),
    foreign key (user_id) references users(id) on delete cascade
		on update cascade
		-- will delete resume when user is deleted or update user ID when in users table
);

-- ============================================================
--  4. JOB LISTINGS
--  Populated by Claude's job-discovery agent per search.
--  Unique per (user_id, source_platform, external_id).
-- ============================================================
CREATE TABLE IF NOT EXISTS job_listings (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED    NOT NULL,
    external_id     VARCHAR(255)        NULL,
    source_platform VARCHAR(100)        NULL,   -- indeed | glassdoor | linkedin
    title           VARCHAR(255)    NOT NULL,
    company         VARCHAR(255)        NULL,
    location        VARCHAR(255)        NULL,
    url             VARCHAR(1024)       NULL,
    salary_raw      VARCHAR(255)        NULL,
    employment_type VARCHAR(100)        NULL,
    is_remote       TINYINT(1)          NULL DEFAULT 0,
    description     TEXT                NULL,
    fetched_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    primary key (id),
    foreign key (user_id) references users(id) 
		on delete cascade
		on update cascade
);

-- ============================================================
--  5. APPLICATIONS
--  One row per submitted application.
--  No status tracking — every row means "applied".
--  cover_letter and resume_variant stored here for reference.
-- ============================================================
CREATE TABLE IF NOT EXISTS applications (
    id             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED    NOT NULL,
    listing_id     INT UNSIGNED    NOT NULL,
    cover_letter   LONGTEXT            NULL,
    resume_variant LONGTEXT            NULL,
    applied_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    primary key(id),
	-- possibly create index to help speed up dahsobard to fetch applied 
    foreign key (user_id) references users(id) on delete cascade,
	foreign key (listing_id) references job_listings(id) on delete cascade
);