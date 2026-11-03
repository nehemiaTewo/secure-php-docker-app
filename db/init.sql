CREATE TABLE IF NOT EXISTS `users` (
  `userID` int NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Status` int DEFAULT '0',
  `RegDatetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `LoginDatetime` datetime DEFAULT NULL,
  `LogoutDatetime` datetime DEFAULT NULL,
  `EmailVerified` int DEFAULT '0',
  `EmailVerificationToken` varchar(64) DEFAULT NULL,
  `TwoFactorPin` varchar(6) DEFAULT NULL,
  `TwoFactorCreatedAt` datetime DEFAULT NULL,
  `TotpSecret` varchar(32) DEFAULT NULL,
  `Role` varchar(20) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;