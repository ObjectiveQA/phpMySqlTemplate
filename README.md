## PhpMySqlTemplate

This template designed to provide a DB layer for shared hosting environment without adding VPS subscription

Code adapted from https://code.tutsplus.com/how-to-build-a-simple-rest-api-in-php--cms-37000t

The intention is that this is basically a DB wrapper, with room for validation and data transformation.

This is not designed to be used for a full back end system, and in due course the intention is to build a node template for that purpose.

### Usage

Suggested to copy files from template into new project folder and verify tests run and postman calls are working before working on project from this template.

Setup steps:

Install MAMP if not already installed.
Migrations are executed manually in PhpMyAdmin.

(NOTE: When running the create-tables.sql SQL, there is no USE keyword to ensure the right DB is selected. Ensure that the correct DB is selected when running this migration!)

MAKE SURE TO Replace the DB name:
(1) in the DROP/CREATE DB statements at the top of the initial migration
(2) in configDev.php (DB_DATABASE_NAME)
(3) in the test const DB_NAME (/test/helpers/consts.js)

Now all the tests should run and postman calls should be successful!

To develop from here:

Update the create-tables.sql file with a relevant migration.
Routes are registered in app.php.
REST request consumption and data validation takes place in the controllers.
The models manage database requests and Database.php builds the SQL and executes the request.

Create (if not copied) configDev.json in /src as (adjusting to point to local db):

```json
{
    "db": {
        "host": "localhost:8889",
        "username": "root",
        "password": "root",
        "databaseName": "PhpMySqlTemplate"
    },
    "appEnv": "dev"
}
```

Going live:

When running in web host a file configProd.json should be added - the reason for name difference is to avoid accidental overwriting when using FileZilla to move files over.

The index.php is just a pointer and is the only file in /public_html. As such is shouldn't change and deployment can be done simply by replacing the app directory, which goes one level up from /public_html (so that the files themselves are not publically accessible).

### Testing

Unit testing is not envisaged for this wrapper, however API testing is highly recommended, as per the attached test suites.

PHP doubtless provides tools for API testing, however at this time due to experience and the limitations of the expected use of this project and of time available Jest and Supertest is the design choice.

The DB should be rebuilt in full on test run and no mocking will take place, the API tests will be fully integrated - again as per the attached test framework.
