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
        <tr v-for="(row, index) in visibleRows" :key="index" class="hover:bg-gray-50"
          :class="isSelected(row) ? 'bg-blue-50 hover:bg-blue-100' : ''">
          <td class="text-left">{{ row.id }}</td>
          <td class="text-left" :data-test="`row-title-${index}`">
            {{ row.title }}
          </td>
          <td class="text-left" data-test="value">
            {{ row.price }} {{ row.currency }}
          </td>
          <td class="text-left" data-test="date">
            {{ row.created_at }}
          </td>
          <td class="text-right">
            <button :data-test="`select-button-${index}`"
              class="bg-blue-100 border-none rounded px-2 cursor-pointer text-gray-700" @click="selectOrderr(row)">
              &#10132;
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="flex flex-row w-auto justify-center mt-8 mx-8">
      <button class="bg-blue-500 border-none rounded text-white px-2 cursor-pointer" @click="decrementPage"
        data-test="prev-page">
        Prev
      </button>
      <p class="mx-8" data-test="page-counter">
        {{ pageNumber + 1 }} of {{ totalPages }}
      </p>
      <button class="bg-blue-500 border-none rounded text-white px-2 cursor-pointer" @click="incrementPage"
        data-test="next-page">
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";

const props = defineProps({
  rows: Array,
  selectedId: String,
});

const emit = defineEmits(["select"]);

const pageNumber = ref(0);
const rowsPerPage = 10;

const incrementPage = () => {
  pageNumber.value++;
};

const decrementPage = () => {
  pageNumber.value--;
};

const selectOrder = (row) => {
  emit("select", row);
};

const isSelected = (row) => {
  return props.selectedId === row.id;
};

const visibleRows = computed(() => {
  const startIndex = pageNumber.value * rowsPerPage;
  return props.rows.slice(startIndex, startIndex + rowsPerPage);
});

const totalPages = computed(() => {
  return Math.ceil(props.rows.length / rowsPerPage);
});
</script>
