const request = require('supertest');
const { REQUEST_BASE } = require('./helpers');

describe('without auth', () => {
    it('returns unauthorized response when required auth key not provided', async() => {
        const response = await request(REQUEST_BASE)
            .get('/')
            .set('TEST_ENV', true);

        expect(response.statusCode).toBe(401);
        expect(response.text).toBe('Unauthorized.');
    });
});
