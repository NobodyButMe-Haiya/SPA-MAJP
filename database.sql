CREATE TABLE `person` (
    `personId` int(11) NOT NULL,
    `name` varchar(150) NOT NULL,
    `age` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `person`
    ADD PRIMARY KEY (`personId`);

ALTER TABLE `person`
    MODIFY `personId` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;