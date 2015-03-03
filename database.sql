create datasbase hackathon2015;
grant all on hackathon2015.* to hackathon@localhost identified by '2015';

drop table checklist_species;
create table checklist_species(
	id int(11) not null auto_increment primary key,
    checklist_code varchar(32) not null,
    scientific_name varchar(128) not null,
    checklist_species_key varchar(255),
    created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `checklist_code` (`checklist_code`,`scientific_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

