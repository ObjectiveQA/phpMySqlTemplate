const { readFile } = require('fs/promises');
const request = require('supertest');
const mysql = require('mysql2/promise');
const { REQUEST_BASE } = require('./helpers');

const DB_NAME = 'PhpMySqlTemplate';
const TEST_DB_NAME = `${DB_NAME}Test`;

const deleteAndCreateDb = async() => {
    const fileData = await readFile('./migrations/20230707112300-create-db.sql');
    const migrationSql = fileData.toString().replaceAll(DB_NAME, TEST_DB_NAME);

    const connection = await mysql.createConnection({
        host: 'localhost',
        port: 8889,
        user: 'root',
        password: 'root',
        multipleStatements: true
    });

    const result = await connection.query(migrationSql);

    if (result.error) throw new Error(error);

    await connection.end();
};

const initialiseDb = async(testDataSql = '') => {
    await deleteAndCreateDb();

    const fileData = await readFile('./migrations/20230707112301-create-tables.sql');
    const migrationSql = fileData.toString().replaceAll(DB_NAME, TEST_DB_NAME);
    const querySql = `${migrationSql}${testDataSql}`;
    
    const connection = await mysql.createConnection({
        host: 'localhost',
        port: 8889,
        user: 'root',
        password: 'root',
        database: TEST_DB_NAME,
        multipleStatements: true
    });

    const result = await connection.query(querySql);

    if (result.error) throw new Error(error);

    await connection.end();
};

const getAllDbUsers = async() => {
    const connection = await mysql.createConnection({
        host: 'localhost',
        port: 8889,
        user: 'root',
        password: 'root',
        database: TEST_DB_NAME
    });

    const result = await connection.query('SELECT * FROM users');

    if (result.error) throw new Error(error);

    await connection.end();

    return result[0];
};

describe('/users', () => {
    describe('GET', () => {
        it('gets a user by id', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com');";
            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .get('/users/1')
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(200);
            expect(response.body.length).toBe(1);
            expect(response.body[0].full_name).toBe('John One');
            expect(response.body[0].email).toBe('johnone@example.com');
        });

        it('gets all users', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com'), ('Jane One', 'janeone@example.com');";
            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .get('/users')
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(200);
            expect(response.body.length).toBe(2);
            expect(response.body[0].full_name).toBe('John One');
            expect(response.body[0].email).toBe('johnone@example.com');
            expect(response.body[1].full_name).toBe('Jane One');
            expect(response.body[1].email).toBe('janeone@example.com');
        });

        it('returns an empty array if non existing id provided', async() => {
            await initialiseDb();
            const response = await request(REQUEST_BASE)
                .get('/users/1')
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(200);
            expect(response.body.length).toBe(0);
        });
    });

    describe('POST', () => {
        it('posts a single user', async() => {
            await initialiseDb();
            const response = await request(REQUEST_BASE)
                .post('/users')
                .send([{ full_name: 'John One', email: 'johnone@example.com' }])
                .set('TEST_ENV', true);
            
            const allDbUsers = await getAllDbUsers();
            
            expect(response.statusCode).toBe(201);
            expect(allDbUsers.length).toBe(1);
            expect(allDbUsers[0].full_name).toBe('John One');
            expect(allDbUsers[0].email).toBe('johnone@example.com');
        });

        it('posts multiple users', async() => {
            await initialiseDb();
            const response = await request(REQUEST_BASE)
                .post('/users')
                .send([
                    { full_name: 'John One', email: 'johnone@example.com' },
                    { full_name: 'Jane One', email: 'janeone@example.com' }
                ])
                .set('TEST_ENV', true);
            
            expect(response.statusCode).toBe(201);

            const allDbUsers = await getAllDbUsers();
            
            expect(allDbUsers.length).toBe(2);
            expect(allDbUsers[0].full_name).toBe('John One');
            expect(allDbUsers[0].email).toBe('johnone@example.com');
            expect(allDbUsers[1].full_name).toBe('Jane One');
            expect(allDbUsers[1].email).toBe('janeone@example.com');
        });

        it('errors if full_name field not provided', async() => {
            await initialiseDb();
            const response = await request(REQUEST_BASE)
                .post('/users')
                .send([{ email: 'johnone@example.com' }])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(400);
            expect(response.text).toBe("Each request body array item must be an object with 'full_name' and 'email' properties.");
        });

        it('errors if email field not provided', async() => {
            await initialiseDb();
            const response = await request(REQUEST_BASE)
                .post('/users')
                .send([{ full_name: 'John One' }])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(400);
            expect(response.text).toBe("Each request body array item must be an object with 'full_name' and 'email' properties.");
        });

        it('errors if duplicate emails provided', async() => {
            await initialiseDb();
            const response = await request(REQUEST_BASE)
                .post('/users')
                .send([
                    { full_name: 'John One', email: 'johnone@example.com' },
                    { full_name: 'John One', email: 'johnone@example.com' }
                ])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(400);
            expect(response.text).toBe('Each object in request body must specify a unique email.');
        });

        it('errors if email already exists in table', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com');";

            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .post('/users')
                .send([{ full_name: 'John One', email: 'johnone@example.com' }])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(400);
            expect(response.text).toBe('Each object in request body must specify an email not already in use.');
        });
    });

    describe('PUT', () => {
        it('updates a single user', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com');";

            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([{ user_id: 1, full_name: 'John Two', email: 'johntwo@example.com' }])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(204);

            const allDbUsers = await getAllDbUsers();

            expect(allDbUsers.length).toBe(1);
            expect(allDbUsers[0].full_name).toBe('John Two');
            expect(allDbUsers[0].email).toBe('johntwo@example.com');
        });

        it('updates multiple users', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com'), ('Jane One', 'janeone@example.com');";

            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([
                    { user_id: 1, full_name: 'John Two', email: 'johntwo@example.com' },
                    { user_id: 2, full_name: 'Jane Two', email: 'janetwo@example.com' }
                ])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(204);

            const allDbUsers = await getAllDbUsers();

            expect(allDbUsers.length).toBe(2);
            expect(allDbUsers[0].full_name).toBe('John Two');
            expect(allDbUsers[0].email).toBe('johntwo@example.com');
            expect(allDbUsers[1].full_name).toBe('Jane Two');
            expect(allDbUsers[1].email).toBe('janetwo@example.com');
        });

        it('reports success if no user found with id provided', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com');";

            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([{ user_id: 2, full_name: 'John One', email: 'johntwo@example.com' }])
                .set('TEST_ENV', true);

                expect(response.statusCode).toBe(204);

                const allDbUsers = await getAllDbUsers();

                expect(allDbUsers.length).toBe(1);
        });

        it('allows emails to be switched within the same batch', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com'), ('Jane One', 'janeone@example.com');";

            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([
                    { user_id: 1, full_name: 'John One', email: 'janeone@example.com' },
                    { user_id: 2, full_name: 'Jane One', email: 'johnone@example.com' }
                ])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(204);
        });

        it('errors if emails duplicated within the same batch', async() => {
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([
                    { user_id: 1, full_name: 'John Two', email: 'johntwo@example.com' },
                    { user_id: 2, full_name: 'Jane Two', email: 'johntwo@example.com' }
                ])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(400);
            expect(response.text).toBe('Each object in request body must specify a unique email.');
        });

        it('errors if email already exists in table', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com'), ('Jane One', 'janeone@example.com');";

            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([{ user_id: 2, full_name: 'Jane One', email: 'johnone@example.com' }])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(400);
            expect(response.text).toBe('Each object in request body must specify an email not already in use.');
        });

        it('errors if user_id not provided', async() => {
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([{ full_name: 'John One', email: 'johnone@example.com' }])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(400);
            expect(response.text).toBe("Each request body array item must contain a valid 'user_id'.");
        });

        it('errors if duplicate user_id provided', async() => {
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([
                    { user_id: 1, full_name: 'John One', email: 'johnone@example.com' },
                    { user_id: 1, full_name: 'John One', email: 'johntwo@example.com' }
                ])
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(400);
            expect(response.text).toBe("Each request body array item must specify a unique 'user_id'.");
        });

        it('errors if id provided is not greater than 0', async() => {
            const response = await request(REQUEST_BASE)
                .put('/users')
                .send([{ user_id: 0, full_name: 'John One', email: 'johnone@example.com' }])
                .set('TEST_ENV', true);

                expect(response.statusCode).toBe(400);
                expect(response.text).toBe("Each request body array item must contain a valid 'user_id'.");
        });
    });

    describe('DELETE', () => {
        it('deletes a user by id', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com');";
            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .delete('/users/1')
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(204);

            const allDbUsers = await getAllDbUsers();

            expect(allDbUsers.length).toBe(0);
        });

        it('reports success if no user found with id provided', async() => {
            const sql = "INSERT INTO users (full_name, email) VALUES ('John One', 'johnone@example.com');";
            await initialiseDb(sql);
            const response = await request(REQUEST_BASE)
                .delete('/users/2')
                .set('TEST_ENV', true);

            expect(response.statusCode).toBe(204);

            const allDbUsers = await getAllDbUsers();

            expect(allDbUsers.length).toBe(1);
        });

        it('errors if no id provided', async() => {
            const response = await request(REQUEST_BASE)
                .delete('/users')
                .set('TEST_ENV', true);

                expect(response.statusCode).toBe(400);
                expect(response.text).toBe('Path element following /users must be a number greater than 0.');
        });

        it('errors if id provided is not greater than 0', async() => {
            const response = await request(REQUEST_BASE)
                .delete('/users/0')
                .set('TEST_ENV', true);

                expect(response.statusCode).toBe(400);
                expect(response.text).toBe('Path element following /users must be a number greater than 0.');
        });
    });
});
