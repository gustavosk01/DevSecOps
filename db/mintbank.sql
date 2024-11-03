DROP DATABASE IF EXISTS mintbank;
CREATE DATABASE mintbank;
USE mintbank;

REVOKE ALL, GRANT OPTION FROM 'mintbank'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON mintbank.* TO 'mintbank'@'%';
FLUSH PRIVILEGES;

CREATE TABLE `users` (
  `cpf` char(14) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `birth_date` char(10) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pass` char(64) NOT NULL,
  `balance` decimal(13,4) NOT NULL DEFAULT 0,
  `image_name` varchar(255) DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `totp_secret` varchar(255) DEFAULT NULL,
  UNIQUE(`email`),
  PRIMARY KEY (`cpf`)
);

INSERT INTO users VALUES ('123.456.789-00', 'Admin', '01/01/2000', '(11) 4002-8922', 'admin@pucparana.com', 'a2ca37fe6fdc490b8f7ce841e1701a169d2b1697c6b5b5c63f94abb8f9b6d6dd', '2500000.00', 'c68920ec2652858ec41bd37d17aadd2574ff352f.jpeg', 0, 'PG3OWHFJKMZR2SRXYH3EU7PTM6R6VVTFPEKRCVSTK7GV2D7GQFPAQOD2I7FSZMU4M6OGIODTP6EKRJ6MGAUPBJVAYYM76GUJRFLBZ4Q');
INSERT INTO users VALUES ('000.000.000-00', 'Teste', '01/01/2000', '(11) 4002-8922', 'teste@pucparana.com', '7efdface6d60805fe4f3078e606e5cb84fb55eaa00a8cc216423ddd23ee08f4b', '0', NULL, 0, NULL);
-- teste@pucparana.com
-- t3st3#2024

CREATE TABLE `transfers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cpf_src` char(14) NOT NULL,
  `cpf_dst` char(14) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(15,4) NOT NULL,
  `message` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO transfers VALUES (1, '000.000.000-00', '123.456.789-00', '2001-09-11 00:00:00', '2500000.00', "<script>alert('Sem XSS por aqui.')</script>");

CREATE TABLE `contact_requests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);