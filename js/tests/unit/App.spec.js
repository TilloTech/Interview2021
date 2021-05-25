import flushPromises from 'flush-promises';

import App from '../../src/App.vue';

global.fetch = jest.fn(() =>
    Promise.resolve({
        json: () => Promise.resolve([1, 2, 3]),
    })
);

describe('App.vue', () => {
    it('The loading state is set to true only while fetching data', async () => {
        const baseState = { orders: [], loading: false }
        const testFn = App.methods.getOrders.bind(baseState)

        expect(baseState.loading).toBe(false)

        testFn()

        expect(baseState.loading).toBe(true)

        await flushPromises();

        expect(baseState.loading).toBe(false)

        expect(baseState.orders).toEqual([1, 2, 3])
    })
})