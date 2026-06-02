<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const message = computed(() => page.props.flash?.message);
const status = computed(() => page.props.flash?.status);
</script>

<template>
    <div
        v-if="message"
        :class="status === 'danger' ? 'bg-red-500' : 'bg-blue-300'"
        class="mb-4 text-white p-4"
    >
        {{ message }}
    </div>
</template>
