CREATE TABLE performances (
  id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  campano_id MEDIUMINT UNSIGNED UNIQUE,
  date DATE,
  society VARCHAR(70), -- max in Campanophile so far is 57 (Aberystwyth Uni)
  county VARCHAR(30),  -- max found 26 (NSW, Aus)
  location VARCHAR(70), -- 68, see Cid 102158
  dedication VARCHAR(50), -- 45
  length SMALLINT UNSIGNED,
  tenor_wt VARCHAR(10),
  changes SMALLINT UNSIGNED,
  method VARCHAR(100), -- guess
  composition TEXT,
  composer VARCHAR(50),
  footnote TEXT,
  -- to match Performance::TYPE_TOWER, Performance::TYPE_HAND (and UNKNOWN)
  type ENUM('1', '2') NOT NULL
);

CREATE TABLE ringers (
  id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  first_name VARCHAR(25),
  middle_names VARCHAR(50),
  last_name VARCHAR(25)
);

CREATE TABLE ringer_performances (
  id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  performance_id MEDIUMINT UNSIGNED REFERENCES performances(id),
  bell TINYINT UNSIGNED,
  ringer_id MEDIUMINT UNSIGNED REFERENCES ringers(id),
  credit VARCHAR(50), -- name as submitted
  conductor BOOLEAN,
  footnote VARCHAR(50)
);

