const {
    DEV_AUTH_KEY,
    REQUEST_BASE,
} = require('./consts');
const {
    getAllDbUsers,
    initialiseDb
} = require('./queries');

module.exports = {
    DEV_AUTH_KEY,
    REQUEST_BASE,
    getAllDbUsers,
    initialiseDb
};