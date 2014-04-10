<?php
interface db {
	const host   = "YOUR_HOST";
	const user   = "YOUR_USERNAME";
	const pwd    = "YOUR_PASSWORD";
	const db     = "YOUR_SERVER_NAME";
	const table  = "YOUR_TABLE_NAME";
}	

interface crypto {
	const shaker = "INSERT_A_RANDOM_STRING_OF_AT_LEAST_32_CHARACTERS_HERE";
	const debug  = "INSERT_ANOTHER_RANDOM_STRING_HERE";
}

/* * *
Add a table to your database

CREATE TABLE IF NOT EXISTS `YOUR_TABLE_NAME` (
  `key` varchar(64) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

* * */