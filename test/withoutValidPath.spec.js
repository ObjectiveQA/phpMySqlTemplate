const request = require('supertest');
const {
    DEV_AUTH_KEY,
    REQUEST_BASE
} = require('./helpers');

describe('without valid path', () => {
    it('returns server error when no path specified', async() => {
        const response = await request(REQUEST_BASE)
            .get('/')
            .set('AUTH_KEY', DEV_AUTH_KEY)
            .set('TEST_ENV', true);

        expect(response.statusCode).toBe(400);
        expect(response.text).toBe('No path specified.');
    });

    it('returns server error when invalid path specified', async() => {
        const response = await request(REQUEST_BASE)
            .get('/abcthisisnotavalidpathcba')
            .set('AUTH_KEY', DEV_AUTH_KEY)
            .set('TEST_ENV', true);

        expect(response.statusCode).toBe(404);
        expect(response.text).toBe('No route matched the requested path.');
    });
});
