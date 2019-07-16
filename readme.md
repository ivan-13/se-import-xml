# Import XML betradar sports and leagues PHP script

## Installation

`
composer install
`

CP `.env.example` file as .env and fill in all of the fields

In order for script to work, it needs to have input data in the inputXML directory.
XML files should have Pascal or Camel cased name with language specified in the filename, separated with underscore i.e.:

`AllTournamentsIDs_DE.xml`
## Create tables

`
php createTables.php
`
## Import data

`
php importXml.php
`