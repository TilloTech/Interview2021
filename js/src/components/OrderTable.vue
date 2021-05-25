<template>
    <div>
        <table class="w-full border-collapse table-auto">
            <thead>
                <tr>
                    <th class="text-left">Order ID</th>
                    <th class="text-left">Title</th>
                    <th class="text-left">Cost</th>
                    <th class="text-left">Placed on</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, index) in visibleRows"
                    :key="index"
                    class="py-1 hover:bg-gray-50"
                    :class="
                        isSelected(row) ? 'bg-blue-50 hover:bg-blue-100' : ''
                    "
                >
                    <td class="text-left">{{ row.id }}</td>
                    <td class="text-left" :data-test="`row-title-${index}`">
                        {{ row.title }}
                    </td>
                    <td class="text-left">
                        {{ row.price }} {{ row.currency }}
                    </td>
                    <td class="text-left">{{ row.created_at }}</td>
                    <td class="text-right">
                        <button
                            :data-test="`select-button-${index}`"
                            class="bg-blue-100 rounded px-2 cursor-pointer text-gray-700"
                            @click="selectOrder(row)"
                        >
                            &#10132;
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="flex flex-row w-auto justify-center pt-8 px-8">
            <button
                class="bg-blue-500 rounded text-white px-2 cursor-pointer"
                @click="decrementPage"
                data-test="prev-page"
            >
                Prev
            </button>
            <p class="mx-8" data-test="page-counter">
                {{ pageNumber + 1 }} of {{ totalPages }}
            </p>
            <button
                class="bg-blue-500 rounded text-white px-2 cursor-pointer"
                @click="incrementPage"
                data-test="next-page"
            >
                Next
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OrderTable',
    props: {
        rows: Array,
        selectedId: String,
    },
    data() {
        return {
            pageNumber: 0,
            rowsPerPage: 10,
        }
    },
    methods: {
        incrementPage() {
            this.pageNumber++
        },
        decrementPage() {
            this.pageNumber--
        },
        selectOrderr(row) {
            this.$emit('select', row)
        },
        isSelected(row) {
            return this.selectedId === row.id
        },
    },
    computed: {
        visibleRows() {
            const startIndex = this.pageNumber * this.rowsPerPage
            return this.rows.slice(startIndex, startIndex + this.rowsPerPage)
        },
        totalPages() {
            return Math.ceil(this.rows.length / this.rowsPerPage)
        },
    },
}
</script>