/**
 * Drupal Planet Links Archive
 *
 * SQL queries to create tables and eliminate duplicates.
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

-- CREATE TABLE SQL:
-- - - - - - - - - - - - - - - -

-- Main table
	CREATE TABLE  `rssingest` (
	 `item_id` VARCHAR( 32 ) NOT NULL ,
	 `feed_url` VARCHAR( 512 ) NOT NULL ,
	 `item_content` VARCHAR( 4000 ) NULL ,
	 `item_title` VARCHAR( 255 ) NOT NULL ,
	 `item_date` TIMESTAMP NOT NULL ,
	 `item_url` VARCHAR( 512 ) NOT NULL ,
	 `item_status` CHAR( 2 ) NOT NULL ,
	 `item_category_id` INT NULL ,
	 `fetch_date` TIMESTAMP NOT NULL
	) ENGINE = MYISAM ;


-- Counter for number of executions
CREATE TABLE `executions` (
`counter` int(9) NOT NULL default '0'
) ENGINE = MYISAM ;

-- - - - - - - - - - - - - - - -


-- Eliminate duplicates (e.g.: cron vs e-mail import, several e-mail imports, etc.)
-- Remove index if any (via PHP my admin)
ALTER IGNORE TABLE `rssingest`   
ADD UNIQUE INDEX (`item_id`);

-- There might be some due to the different MD5 function in PHP vs Python. In that case, run:

ALTER IGNORE TABLE `rssingest`   
ADD UNIQUE INDEX (`item_title`);

ALTER IGNORE TABLE `rssingest`   
ADD UNIQUE INDEX (`item_url`);

-- The last one should be enough, but we run all of them just in case. Remove later all the indexes,
-- since this will be controlled via the web-scritps afterwards

DROP INDEX item_id ON rssingest;
DROP INDEX item_title ON rssingest;
DROP INDEX item_url ON rssingest;

