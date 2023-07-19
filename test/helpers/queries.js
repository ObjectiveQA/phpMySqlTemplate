const { readFile } = require('fs/promises');
const mysql = require('mysql2/promise');
const {
    DB_NAME,
    TEST_DB_NAME
} = require('./consts');

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

const getAllDbUsers = () => queryDb('SELECT * FROM users');

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

const queryDb = async(query) => {
    const connection = await mysql.createConnection({
        host: 'localhost',
        port: 8889,
        user: 'root',
        password: 'root',
        database: TEST_DB_NAME
    });

    const result = await connection.query(query);

    if (result.error) throw new Error(error);

    await connection.end();

    return result[0];
}

module.exports = {
    getAllDbUsers,
    initialiseDb
};
