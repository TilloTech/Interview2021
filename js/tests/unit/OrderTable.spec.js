import { shallowMount } from '@vue/test-utils';

import OrderTable from '../../src/components/OrderTable.vue';

import { TEST_ORDERS_SHORT, TEST_ORDERS_LONG } from '../data/TestOrders'

describe('OrderTable.vue', () => {
    it('Allows you to click an order to see more detail', async () => {
        const wrapper = shallowMount(OrderTable, {
            propsData: {
                rows: TEST_ORDERS_SHORT
            }
        })

        const button = wrapper.find('[data-test="select-button-0');

        expect(button.exists()).toBe(true);

        await button.trigger('click');

        const event = wrapper.emitted('select')

        expect(event.length).toBe(1)

        expect(event[0][0].id).toBe(TEST_ORDERS_SHORT[0].id)
    });

    it('Has pagination on which works correctly', async () => {
        const wrapper = shallowMount(OrderTable, {
            propsData: {
                rows: TEST_ORDERS_LONG
            }
        })

        const pageCounter = wrapper.find('[data-test=page-counter]')

        expect(pageCounter.text()).toBe('1 of 5')

        const nextButton = wrapper.find('[data-test=next-page]')

        await nextButton.trigger('click')

        expect(pageCounter.text()).toBe('2 of 5')

        const prevButton = wrapper.find('[data-test=prev-page]')

        await prevButton.trigger('click')

        expect(pageCounter.text()).toBe('1 of 5')
    });

    it('Has pagination on which won\'t show pages below page 1', async () => {
        const wrapper = shallowMount(OrderTable, {
            propsData: {
                rows: TEST_ORDERS_LONG
            }
        })

        const pageCounter = wrapper.find('[data-test=page-counter]')

        expect(pageCounter.text()).toBe('1 of 5')

        const nextButton = wrapper.find('[data-test=next-page]')

        await nextButton.trigger('click')

        expect(pageCounter.text()).toBe('2 of 5')

        const prevButton = wrapper.find('[data-test=prev-page]')

        await prevButton.trigger('click')

        expect(pageCounter.text()).toBe('1 of 5')

        await prevButton.trigger('click')

        expect(pageCounter.text()).not.toBe('0 of 5')

        expect(pageCounter.text()).toBe('1 of 5')
    });

    it('Has pagination on which won\'t show pages above the maximum page', async () => {
        const wrapper = shallowMount(OrderTable, {
            propsData: {
                rows: TEST_ORDERS_LONG
            }
        })

        const pageCounter = wrapper.find('[data-test=page-counter]')

        expect(pageCounter.text()).toBe('1 of 5')

        const nextButton = wrapper.find('[data-test=next-page]')

        await nextButton.trigger('click')

        expect(pageCounter.text()).toBe('2 of 5')

        await nextButton.trigger('click')

        expect(pageCounter.text()).toBe('3 of 5')

        await nextButton.trigger('click')

        expect(pageCounter.text()).toBe('4 of 5')

        await nextButton.trigger('click')

        expect(pageCounter.text()).toBe('5 of 5')

        await nextButton.trigger('click')

        expect(pageCounter.text()).not.toBe('6 of 5')

        expect(pageCounter.text()).toBe('5 of 5')
    });

    it('Shows the order title with a capitalised first letter', () => {
        const wrapper = shallowMount(OrderTable, {
            propsData: {
                rows: TEST_ORDERS_SHORT
            }
        })

        const firstRowTitle = wrapper.find('[data-test="row-title-0');

        expect(firstRowTitle.exists()).toBe(true);

        expect(firstRowTitle.text()).toBe('Velit cupidatat aliqua dolor veniam')

        const thirdRowTitle = wrapper.find('[data-test="row-title-2');

        expect(thirdRowTitle.exists()).toBe(true);

        expect(thirdRowTitle.text()).toBe('Ad occaecat aliquip fugiat est')
    });
})