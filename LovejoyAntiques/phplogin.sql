CREATE DATABASE IF NOT EXISTS `phplogin` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `phplogin`;


CREATE TABLE IF NOT EXISTS `accounts` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
  	`username` varchar(50) NOT NULL,
	`fullname` varchar(100) NOT NULL,
  	`password` varchar(255) NOT NULL,
  	`email` varchar(100) NOT NULL UNIQUE,
	`telephone` varchar(11) NOT NULL,
	`activation_code` varchar(50) NOT NULL DEFAULT '',
	`securityQuestion` varchar(100) NOT NULL DEFAULT '',
	`securityAnswer` varchar(100) NOT NULL DEFAULT '',
	`role` enum('Member','Admin') NOT NULL DEFAULT 'Member',
	`reset` varchar(50) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluations` (
	`evalid` int(11) NOT NULL AUTO_INCREMENT,
	`description` varchar(255) NOT NULL,
	`contactDetail` varchar(100) NOT NULL,
	`image_url` varchar(255) NOT NULL,
	`userid` int(11) NOT NULL,
	PRIMARY KEY (`evalid`),
	FOREIGN KEY (`userid`) REFERENCES `accounts`(`id`) ON DELETE CASCADE
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `login_attempts` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`ip_address` varchar(255) NOT NULL,
	`attempts_left` tinyint(1) NOT NULL DEFAULT '5',
	`date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `login_attempts` ADD UNIQUE KEY `ip_address` (`ip_address`);

INSERT INTO `accounts` (`id`, `username`, `fullname`, `password`, `email`, `telephone`, `securityQuestion` ,`role`) VALUES
(1, 'admin', 'Admin Admin','$2y$10$ZU7Jq5yZ1U/ifeJoJzvLbenjRyJVkSzmQKQc.X0KDPkfR3qs/iA7O', 'example@admin.com', '01234567890', ,'What is your Mothers maiden name?','Cowen' ,'Admin'),
(2, 'member', 'Member Member', '$2y$10$7vKi0TjZimZyp/S5aCtK0eLsGagyIJVfpzGSFgRSqDGkJMxqoIYV.', 'member@example.com', '01234567891', 'What is your Mothers maiden name?' ,'Seagrove' ,'Member');
INSERT INTO evaluations (evalid, description, contactDetail, image_url, userid) VALUES(1, 'Hello AAAAAAAAAAAAAAAA','01234567890','IMG-61a97f291edd19.53109366.png',8);