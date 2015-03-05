create database hackathon2015;
grant all on hackathon2015.* to hackathon@localhost identified by '2015';

use hackathon2015;

drop table checklist_species;
create table checklist_species(
	id int(11) not null auto_increment primary key,
    checklist_code varchar(32),
    scientific_name varchar(128) not null,
    checklist_species_key varchar(255),
    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `checklist_code` (`checklist_code`,`scientific_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

drop table checklist_matches;
create table checklist_matches (
	id int(11) not null auto_increment primary key,
	checklist_species_id int(11) not null,
	match_provider varchar(64) not null,
	match_provider_key varchar(64),
	match_value varchar(16) not null,
    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `checklist_species_1` (`checklist_species_id`),
  INDEX `checklist_species_2` (`checklist_species_id`,`match_provider`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

drop table checklist_gbif_matches;
create table checklist_gbif_matches (
	id int(11) not null auto_increment primary key,
	checklist_species_id int(11) not null,
	gbif_key varchar(64),
	basisOfRecord_HUMAN_OBSERVATION int(5),
	basisOfRecord_OBSERVATION int(5),
	basisOfRecord_PRESERVED_SPECIMEN int(5),
	basisOfRecord_UNKNOWN int(5),
	basisOfRecord_FOSSIL_SPECIMEN int(5),
	basisOfRecord_LIVING_SPECIMEN int(5),
	basisOfRecord_MACHINE_OBSERVATION int(5),
	basisOfRecord_LITERATURE int(5),
	basisOfRecord_MATERIAL_SAMPLE int(5),
	date_NoDate int(5),
	date_All int(5),
	date_1970_2020 int(5),
	date_2010_2020 int(5),
    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `checklist_species_1` (`checklist_species_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


create database hack;
grant all on hack.* to hack@localhost identified by 'h@ck';
