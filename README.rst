==============
Campanoscraper
==============

Requirements
------------
* PHP 5.2 (may work with lower versions, but is untested)
* Curl PHP extension - for submission of search queries to the campanophile website
* MySQL Database and PHP extension - for local saving of data

Classes
-------
* Campanophile - singleton class representing a session on the Campanophile website, has functions for accessing various features of the website
* Database storage stuff: (yes, project focus slippage!)

  * Database - misc local database functions
  * DatabaseRecord - misc functions for storing objects in the database
  * RecordCollection - essentially an array

Data Models
-----------
* Performance - represents peal/quarter/other performance
* RingerPerformance - links a Ringer with a Performance, stores individual footnotes, conductor flag, etc
* Ringer - an identifier for a single ringer, stores name

Using
-----
::

  require('Campanoscraper/load.php');
  $camp = Campanophile::getInstance();
  $quarter = $camp->get_performance(12345);

Contact
-------
Thomas Wood (Github username - edgemaster)

