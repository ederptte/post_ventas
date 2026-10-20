<script setup>
import ConfirmarModal from "@/components/ConfirmarModal.vue";
import ToastContainer from "@/components/ToastContainer.vue";
</script>

<template class="capitalize">
    <RouterView />
    <ConfirmarModal />
    <ToastContainer />
</template>
