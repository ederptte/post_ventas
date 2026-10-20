<script setup>
import AppSidebar from '@/components/AppSidebar.vue'
</script>

<template>
  <div class="flex min-h-screen bg-gray-300">
    <AppSidebar />

    <main class="min-w-0 flex-1 p-3">
      <RouterView />
    </main>
  </div>
</template>