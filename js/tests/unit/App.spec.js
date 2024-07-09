import { vi, describe, it, expect } from "vitest";
import { shallowMount, flushPromises, mount } from "@vue/test-utils";

import App from "../../src/App.vue";
import { TEST_ORDERS_SHORT } from "../data/TestOrders";

global.fetch = vi.fn(() =>
  Promise.resolve({
    json: () => new Promise((resolve) => resolve(TEST_ORDERS_SHORT)),
  })
);

describe("App.vue", () => {
  it("The loading state is set to true only while fetching data", async () => {
    const wrapper = shallowMount(App);

    expect(wrapper.vm.loading).toBe(true);

    await flushPromises();

    expect(wrapper.vm.loading).toBe(false);

    expect(wrapper.vm.orders).toEqual(TEST_ORDERS_SHORT);
  });
});
