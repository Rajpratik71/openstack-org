DROP TABLE IF EXISTS `Member`;
CREATE TABLE `Member` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ClassName` enum('Member') CHARACTER SET utf8 DEFAULT 'Member',
  `Created` datetime DEFAULT NULL,
  `LastEdited` datetime DEFAULT NULL,
  `FirstName` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `Surname` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `Email` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `Password` varchar(160) CHARACTER SET utf8 DEFAULT NULL,
  `RememberLoginToken` varchar(160) CHARACTER SET utf8 DEFAULT NULL,
  `NumVisit` int(11) NOT NULL DEFAULT '0',
  `LastVisited` datetime DEFAULT NULL,
  `Bounced` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `AutoLoginHash` varchar(160) CHARACTER SET utf8 DEFAULT NULL,
  `AutoLoginExpired` datetime DEFAULT NULL,
  `PasswordEncryption` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `Salt` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `PasswordExpiry` date DEFAULT NULL,
  `LockedOutUntil` datetime DEFAULT NULL,
  `Locale` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  `FailedLoginCount` int(11) NOT NULL DEFAULT '0',
  `DateFormat` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `TimeFormat` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `Address` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Suburb` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `State` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `Postcode` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `Country` varchar(2) CHARACTER SET utf8 DEFAULT NULL,
  `SecondEmail` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `ThirdEmail` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `HasBeenEmailed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ShirtSize` enum('Extra Small','Small','Medium','Large','XL','XXL') CHARACTER SET utf8 DEFAULT 'Extra Small',
  `CompanyAffliations` mediumtext CHARACTER SET utf8,
  `StatementOfInterest` mediumtext CHARACTER SET utf8,
  `FoodPreference` mediumtext CHARACTER SET utf8,
  `OtherFood` mediumtext CHARACTER SET utf8,
  `IRCHandle` mediumtext CHARACTER SET utf8,
  `TwitterName` mediumtext CHARACTER SET utf8,
  `Projects` mediumtext CHARACTER SET utf8,
  `OtherProject` mediumtext CHARACTER SET utf8,
  `SubscribedToNewsletter` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `City` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `Bio` mediumtext CHARACTER SET utf8,
  `PhotoID` int(11) DEFAULT '0',
  `OrgID` int(11) DEFAULT '0',
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `SortOrder` int(11) NOT NULL DEFAULT '0',
  `JobTitle` mediumtext CHARACTER SET utf8,
  `DisplayOnSite` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Role` mediumtext CHARACTER SET utf8,
  `LinkedInProfile` mediumtext CHARACTER SET utf8,
  `Gender` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `TypeOfDirector` mediumtext CHARACTER SET utf8,
  `PresentationList` mediumtext CHARACTER SET utf8,
  `ActiveSummitID` int(11) NOT NULL DEFAULT '0',
  `IdentityURL` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `TempIDHash` varchar(160) CHARACTER SET utf8 DEFAULT NULL,
  `TempIDExpired` datetime DEFAULT NULL,
  `ShowDupesOnProfile` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `AuthenticationToken` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `AuthenticationTokenExpire` int(11) NOT NULL DEFAULT '0',
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `EmailVerified` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `EmailVerifiedTokenHash` mediumtext CHARACTER SET utf8,
  `EmailVerifiedDate` datetime DEFAULT NULL,
  `VotingListID` int(11) DEFAULT '0',
  `LegacyMember` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `AskOpenStackUsername` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `ResignDate` datetime DEFAULT NULL,
  `ProfileLastUpdate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `email_unique_idx` (`Email`),
  KEY `Email` (`Email`),
  KEY `ClassName` (`ClassName`),
  KEY `Member_CompanyAffliations` (`CompanyAffliations`(32)),
  KEY `PhotoID` (`PhotoID`),
  KEY `OrgID` (`OrgID`),
  KEY `CompanyID` (`CompanyID`),
  KEY `ActiveSummitID` (`ActiveSummitID`),
  KEY `AuthenticationToken` (`AuthenticationToken`),
  KEY `VotingListID` (`VotingListID`),
  KEY `SecondEmail` (`SecondEmail`) USING BTREE,
  KEY `ThirdEmail` (`ThirdEmail`) USING BTREE,
  KEY `FirstName` (`FirstName`) USING BTREE,
  KEY `Surname` (`Surname`) USING BTREE,
  KEY `FirstName_Surname` (`FirstName`,`Surname`) USING BTREE,
  FULLTEXT KEY `SearchFields` (`FirstName`,`Surname`)
) ENGINE=InnoDB AUTO_INCREMENT=80922 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `Group_Members`;
CREATE TABLE `Group_Members` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GroupID` int(11) NOT NULL DEFAULT '0',
  `MemberID` int(11) NOT NULL DEFAULT '0',
  `SortIndex` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `GroupID` (`GroupID`),
  KEY `MemberID` (`MemberID`)
) ENGINE=InnoDB AUTO_INCREMENT=220023 DEFAULT CHARSET=latin1;

INSERT INTO Member
(`ID`,`ClassName`,`Created`,`LastEdited`,`FirstName`,`Surname`,`Email`,`Password`,`PasswordEncryption`,`Active`,`EmailVerified`,`EmailVerifiedDate`)
VALUES (1,'Member',NOW(),NOW(),'admin','admin','admin@admin.com','1qaz2wsx','none',1,1,NOW());

INSERT INTO `Group_Members` (`ID`,`GroupID`,`MemberID`,`SortIndex`) VALUES (1,2,1,0);


UPDATE `File` SET CloudStatus='Live';