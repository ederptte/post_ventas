<script setup>
defineProps({
  columns: { type: Array, required: true },
  rows: { type: Array, default: () => [] },
  rowKey: { type: String, default: 'id' },
  loading: { type: Boolean, default: false },
  clickable: { type: Boolean, default: false },
  emptyText: { type: String, default: 'No hay registros.' },
  dense: { type: Boolean, default: false },
})

const emit = defineEmits(['row-click'])
</script>

<template>
  <div class="overflow-x-auto rounded-lg border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th
          v-for="column in columns"
          :key="column.key"
          scope="col"
          class="px-4 font-medium text-gray-600 whitespace-nowrap"
          :class="[
            dense ? 'py-2' : 'py-3',
            column.align === 'right' ? 'text-right' : column.align === 'center' ? 'text-center' : 'text-left',
          ]"
        >
            {{ column.label }}
          </th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 bg-white">
        <tr v-if="loading">
          <td :colspan="columns.length" class="px-4 py-4 text-center text-gray-500">
            Cargando...
          </td>
        </tr>

        <tr v-else-if="!rows.length">
          <td :colspan="columns.length" class="px-4 py-4 text-center text-gray-500">
            {{ emptyText }}
          </td>
        </tr>

        <template v-else>
          <tr
            v-for="row in rows"
            :key="row[rowKey]"
            class="transition-colors"
            :class="clickable ? 'cursor-pointer hover:bg-gray-100' : ''"
            :tabindex="clickable ? 0 : undefined"
            @click="clickable && emit('row-click', row)"
            @keydown.enter="clickable && emit('row-click', row)"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-2 text-gray-800"
              :class="[
                dense ? 'py-1' : 'py-2',
                column.align === 'right' ? 'text-right' : column.align === 'center' ? 'text-center' : 'text-left',
              ]"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                {{ row[column.key] }}
              </slot>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>