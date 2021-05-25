import { shallowMount } from '@vue/test-utils';

import OrderDetail from '../../src/components/OrderDetail.vue';

import { TEST_ORDERS_SINGLE } from '../data/TestOrders'

describe('OrderDetail.vue', () => {
    it('Shows the title of the order with a capital letter at the start', () => {
        const wrapper = shallowMount(OrderDetail, {
            propsData: {
                order: TEST_ORDERS_SINGLE[0]
            }
        })

        const title = wrapper.find('[data-test="title"]');

        expect(title.text()).toContain('Fugiat magna Lorem aliquip qui');
    });

    it('Has a website link with the correct href attribute', () => {
        const wrapper = shallowMount(OrderDetail, {
            propsData: {
                order: TEST_ORDERS_SINGLE[0]
            }
        })

        const link = wrapper.find('[data-test="website"]');

        expect(link.attributes().href).not.toBeUndefined();

        expect(link.attributes().href).toBe('https://example.com/products/5889b0a6797714883c501c23')
    });

    it('Has clickable telephone and email links', () => {
        const wrapper = shallowMount(OrderDetail, {
            propsData: {
                order: TEST_ORDERS_SINGLE[0]
            }
        })

        const email = wrapper.find('[data-test="email"]');

        expect(email.attributes().href).not.toBeUndefined();

        expect(email.attributes().href).toContain('mailto:')

        const tel = wrapper.find('[data-test="tel"]');

        expect(tel.attributes().href).not.toBeUndefined();

        expect(tel.attributes().href).toContain('tel:')
    });
})