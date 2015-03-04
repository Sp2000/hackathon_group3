create datasbase hackathon2015;
grant all on hackathon2015.* to hackathon@localhost identified by '2015';

drop table checklist_species;
create table checklist_species(
	id int(11) not null auto_increment primary key,
    checklist_code varchar(32),
    scientific_name varchar(128) not null,
    checklist_species_key varchar(255),
    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `checklist_code` (`checklist_code`,`scientific_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

create table checklist_gbif_matches (
	id int(11) not null auto_increment primary key,
	checklist_species_id int(11) not null,
	gbif_key varchar(64),
	count_name varchar(64) not null,
	count_value int(11) not null,
    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `checklist_species_1` (`checklist_species_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;