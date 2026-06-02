<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { nl2br } from '@/common';
import { router } from '@inertiajs/vue3'

defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const deleteItem = id => {
    router.delete(route('items.destroy', { item: id }), {
            onBefore: () => confirm('本当に削除しますか？')
    })
}
</script>

<template>
    <Head title="商品詳細" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">商品詳細</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <dl class="w-full max-w-md divide-y divide-gray-200">
                            <div class="py-4">
                                <dt class="text-sm font-medium text-gray-500">ID</dt>
                                <dd class="mt-1 text-base text-gray-900">{{ item.id }}</dd>
                            </div>
                            <div class="py-4">
                                <dt class="text-sm font-medium text-gray-500">商品名</dt>
                                <dd class="mt-1 text-base text-gray-900">{{ item.name }}</dd>
                            </div>
                            <div class="py-4">
                                <dt class="text-sm font-medium text-gray-500">メモ</dt>
                                <dd v-html="nl2br( item.memo || '未入力') "class="mt-1 whitespace-pre-wrap text-base text-gray-900">
                                    
                                </dd>
                            </div>
                            <div class="py-4">
                                <dt class="text-sm font-medium text-gray-500">商品価格</dt>
                                <dd class="mt-1 text-base text-gray-900">{{ item.price }}</dd>
                            </div>
                            <div class="py-4">
                                <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                                <dd class="mt-1 text-base text-gray-900">
                                    <span v-if="item.is_selling === 1">販売中</span>
                                    <span v-else>停止中</span>
                                </dd>
                            </div>
                        </dl>
                        <div class="flex items-center gap-2">
                            <Link as="button" :href="route('items.edit', { item: item.id })" class="rounded-md bg-indigo-600 text-sm font-semibold text-white shadow-sm">
                                編集する</Link>
                        </div>
                        <div class="mt-10 flex items-center gap-2">
                            <button @click="deleteItem(item.id)" class="rounded-md bg-red-600 text-sm font-semibold text-white shadow-sm">
                                削除する</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
