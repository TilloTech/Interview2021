<template>
    <div class="p-30">
        <div>
            <h1 class="text-4xl text-center mb-4">Orders!</h1>
        </div>
        <div v-if="loading">Loading...</div>
        <OrderTable
            v-else
            :rows="orders"
            :selectedId="selectedOrder && selectedOrder.id"
            @select="handleSelectOrder"
        />
        <OrderDetail
            class="mt-8"
            v-if="selectedOrder"
            :order="selectedOrder"
            @close="selectedOrder = undefined"
        />
    </div>
</template>

<script>
import OrderTable from './components/OrderTable.vue'
import OrderDetail from './components/OrderDetail.vue'
export default {
    name: 'App',
    components: {
        OrderTable,
        OrderDetail,
    },
    data() {
        return {
            orders: [],
            selectedOrder: undefined,
            loading: false,
        }
    },
    mounted() {
        this.getOrders()
    },
    methods: {
        getOrders() {
            this.loading = true
            fetch('./orders.json')
                .then((res) => res.json())
                .then((ordersData) => {
                    this.orders = ordersData
                })
            this.loading = false
        },
        handleSelectOrder(order) {
            this.selectedOrder = order
        },
    },
}
</script>
