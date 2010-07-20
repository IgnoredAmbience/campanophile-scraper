CREATE TABLE performances (
  campano_id MEDIUMINT UNSIGNED PRIMARY KEY,
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
  ringer_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  first_name VARCHAR(25),
  middle_names VARCHAR(50),
  last_name VARCHAR(25)
);

CREATE TABLE performance_ringers (
  performance_id MEDIUMINT UNSIGNED REFERENCES performances(campano_id),
  ringer_id MEDIUMINT UNSIGNED REFERENCES ringers(ringer_id),
  bell TINYINT UNSIGNED,
  credit VARCHAR(50), -- name as submitted
  conductor BOOLEAN,
  footnote VARCHAR(50),
  PRIMARY KEY (performance_id, bell)
);

