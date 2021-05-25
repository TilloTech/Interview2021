<template>
    <div class="flex flex-col bg-gray-100 rounded p-8 text-gray-800">
        <div class="flex flex-row justify-between items-start">
            <div>
                <h1 class="text-2xl text-medium mb-1" data-test="title">
                    {{ order.title }}
                    <span class="text-gray-500">({{ order.id }})</span>
                </h1>
                <h2 class="text-xl text-medium mb-2">
                    {{ order.price }} {{ order.currency }}
                </h2>
                <p class="mb-4 w-1/2">{{ order.description }}</p>
                <p class="mb-4">UUID: {{ order.uuid }}</p>
                <p class="mb-2">
                    <a
                        class="text-blue-500 underline cursor-pointer"
                        :href="order.website"
                        data-test="website"
                        >Website</a
                    >
                </p>
            </div>
            <button
                class="bg-blue-500 rounded text-white px-2 cursor-pointer"
                @click="$emit('close')"
            >
                Close
            </button>
        </div>
        <div class="flex flex-col">
            <h2 class="text-xl text-medium">Customer details</h2>
            <h3>
                {{ order.customer.name.first }} {{ order.customer.name.last }}
            </h3>
            <p>
                Email:
                <a
                    class="text-blue-500 underline cursor-pointer"
                    :href="order.customer.email"
                    data-test="email"
                    >{{ order.customer.email }}</a
                >
            </p>
            <p>
                Tel:
                <a
                    class="text-blue-500 underline cursor-pointer"
                    :href="order.customer.phone"
                    data-test="tel"
                    >{{ order.customer.phone }}</a
                >
            </p>
            <div class="flex flex-row mt-4">
                <div class="w-1/4">
                    <h4 class="text-l font-medium">Billing Address</h4>
                    <p>{{ order.customer.billing_address.street }}</p>
                    <p>{{ order.customer.billing_address.city }}</p>
                    <p>{{ order.customer.billing_address.county }}</p>
                    <p>{{ order.customer.billing_address.postcode }}</p>
                </div>
                <div class="w-1/4">
                    <h4 class="text-l font-medium">Shipping Address</h4>
                    <p>{{ order.customer.shipping_address.street }}</p>
                    <p>{{ order.customer.shipping_address.city }}</p>
                    <p>{{ order.customer.shipping_address.county }}</p>
                    <p>{{ order.customer.shipping_address.postcode }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OrderDetail',
    props: {
        order: Object,
    },
}
</script>