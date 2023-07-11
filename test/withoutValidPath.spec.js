const request = require('supertest');
const { REQUEST_BASE } = require('./helpers');

describe('without valid path', () => {
    it('returns server error when no path specified', async() => {
        const response = await request(REQUEST_BASE)
            .get('/')
            .set('TEST_ENV', true);

        expect(response.statusCode).toBe(400);
        expect(response.text).toBe('No path specified.');
    });

    it('returns server error when invalid path specified', async() => {
        const response = await request(REQUEST_BASE)
            .get('/abcthisisnotavalidpathcba')
            .set('TEST_ENV', true);

        expect(response.statusCode).toBe(404);
        expect(response.text).toBe('No route matched the requested path.');
    });
});
