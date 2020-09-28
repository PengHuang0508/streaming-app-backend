<?php
$createUserTableQuery = "CREATE TABLE IF NOT EXISTS user(
  username VARCHAR(50) NOT NULL PRIMARY KEY,
  permission VARCHAR(20) DEFAULT 'free',
  email VARCHAR(50) NOT NULL
  );
";

$createMediaTableQuery = "CREATE TABLE IF NOT EXISTS media(
  media_key VARCHAR(70) NOT NULL PRIMARY KEY,
  thumbnail_url VARCHAR(250) NULL,
  title VARCHAR(50) NOT NULL,
  media_description VARCHAR(120) NULL,
  uploaded_by VARCHAR(50) DEFAULT 'admin' NOT NULL,
  min_permission VARCHAR(20) DEFAULT 'free' NOT NULL,
  view INT(5) DEFAULT 0,
  duration TIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  FOREIGN KEY (uploaded_by) REFERENCES user(username) ON DELETE CASCADE
  );
";
