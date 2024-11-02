<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
  users: Array
})

</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
           <div class="grid grid-cols-4 gap-4"  >
            <div v-for="user in users" :key="user.id">
                <Link :href="route('chat.show', user.id)">
                    {{ user.name }}
                </Link>
            </div>
           </div>
        </div>
    </AuthenticatedLayout>
</template>
