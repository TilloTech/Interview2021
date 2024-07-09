<template>
  <div class="p-30 font-sans">
    <div>
      <h1 class="text-4xl text-center mb-4">Orders!</h1>
    </div>
    <div v-if="loading">Loading...</div>
    <OrderTable v-else :rows="orders" :selectedId="selectedOrder && selectedOrder.id" @select="handleSelectOrder" />
    <OrderDetail class="mt-8" v-if="selectedOrder" :order="selectedOrder" @close="selectedOrder = undefined" />
  </div>
</template>

<script setup>
import OrderTable from "./components/OrderTable.vue";
import OrderDetail from "./components/OrderDetail.vue";
import { onMounted, ref } from "vue";

const orders = ref([]);
const selectedOrder = ref(undefined);
const loading = ref(false);

onMounted(() => {
  getOrders();
});

const getOrders = () => {
  loading.value = true;
  fetch("./orders.json")
    .then((res) => res.json())
    .then((ordersData) => {
      orders.value = ordersData;
    });
  loading.value = false;
};

const handleSelectOrder = (order) => {
  selectedOrder.value = order;
};
</script>
