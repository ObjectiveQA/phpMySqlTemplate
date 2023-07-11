## PhpMySqlTemplate

This template designed to provide a DB layer for shared hosting environment without adding VPS subscription

Code adapted from https://code.tutsplus.com/how-to-build-a-simple-rest-api-in-php--cms-37000t

The intention is that this is basically a DB wrapper, with room for validation and data transformation.

This is not designed to be used for a full back end system, and in due course the intention is to build a node template for that purpose.

### Usage

Install MAMP if not already installed.

Update the create-tables.sql file with a relevant migration.

NOTE: When running the create-tables.sql SQL, there is no USE keyword to ensure the right DB is selected. Ensure that the correct DB is selected when running this migration!

Update startup/config.php with the DB details.

REST request consumption and data validation takes place in the controllers.

The models manage database requests and Database.php builds the SQL and executes the request.

MAKE SURE TO Replace the DB name:
(1) in the DROP/CREATE DB statements at the top of the initial migration
(2) in config.php (DB_DATABASE_NAME and DB_TEST_DATABASE_NAME)
(3) in the test const DB_NAME

### Testing

Unit testing is not envisaged for this wrapper, however API testing is recommended.

PHP doubtless provides tools for API testing, however at this time due to experience and the limitations of the expected use of this project and of time available Jest and Supertest is the design choice.

The DB should be rebuilt in full on test run and no mocking will take place, the API tests will be fully integrated.
