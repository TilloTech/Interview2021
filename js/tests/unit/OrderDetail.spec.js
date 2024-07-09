import { shallowMount } from "@vue/test-utils";
import { describe, it, expect } from "vitest";

import OrderDetail from "../../src/components/OrderDetail.vue";

import { TEST_ORDERS_SINGLE, TEST_ORDERS_SHORT } from "../data/TestOrders";
import OrderDetail from "../../src/components/OrderDetail.vue";

describe("OrderDetail.vue", () => {
  it("Shows the title of the order with a capital letter at the start", () => {
    const wrapper = shallowMount(OrderDetail, {
      props: {
        order: TEST_ORDERS_SINGLE[0],
      },
    });

    const title = wrapper.find('[data-test="title"]');

    expect(title.text()).toContain("Fugiat magna Lorem aliquip qui");
  });

  it("Has a website link with the correct href attribute", () => {
    const wrapper = shallowMount(OrderDetail, {
      props: {
        order: TEST_ORDERS_SINGLE[0],
      },
    });

    const link = wrapper.find('[data-test="website"]');

    expect(link.attributes().href).not.toBeUndefined();

    expect(link.attributes().href).toBe(
      "https://example.com/products/5889b0a6797714883c501c23"
    );
  });

  it("Has clickable telephone and email links", () => {
    const wrapper = shallowMount(OrderDetail, {
      props: {
        order: TEST_ORDERS_SINGLE[0],
      },
    });

    const email = wrapper.find('[data-test="email"]');

    expect(email.attributes().href).not.toBeUndefined();

    expect(email.attributes().href).toContain("mailto:");

    const tel = wrapper.find('[data-test="tel"]');

    expect(tel.attributes().href).not.toBeUndefined();

    expect(tel.attributes().href).toContain("tel:");
  });

  it.each([
    { order: TEST_ORDERS_SHORT[0], moneyValue: "€6.91" },
    { order: TEST_ORDERS_SHORT[1], moneyValue: "$4.10" },
    { order: TEST_ORDERS_SHORT[2], moneyValue: "£8.74" },
  ])(
    "Shows $order.currency value of $order.price formatted as $moneyValue",
    ({ order, moneyValue }) => {
      const wrapper = shallowMount(OrderDetail, {
        props: {
          order,
        },
      });

      const value = wrapper.find('[data-test="value"]');

      expect(value.text()).toBe(moneyValue);
    }
  );
});
