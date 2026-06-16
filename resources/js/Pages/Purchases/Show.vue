<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { computed } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
    items: Array,
    order: Array,
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const totalPrice = computed(() => props.order[0]?.total ?? 0);
</script>

<template>
    <Head title="購買履歴　詳細画面" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">購買履歴　詳細画面</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 space-y-8">

                        <div v-if="$page.props.flash.message" class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                            {{ $page.props.flash.message }}
                        </div>

                        <fieldset class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">日付</label>
                                <div class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                                    {{ dayjs(order[0].created_at).format('YYYY/MM/DD') }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">顧客名</label>
                                <div class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                                    {{ order[0].customer_name }}
                                </div>
                            </div>
                        </fieldset>

                        <div class="space-y-3">
                            <h3 class="text-sm font-medium">商品・サービス</h3>

                            <div class="overflow-x-auto rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="w-[100px]">Id</TableHead>
                                            <TableHead>商品名</TableHead>
                                            <TableHead class="text-right">金額</TableHead>
                                            <TableHead>数量</TableHead>
                                            <TableHead class="text-right">小計</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="item in items"
                                            :key="item.pivot_id"
                                        >
                                            <TableCell class="font-medium">{{ item.pivot_id }}</TableCell>
                                            <TableCell>{{ item.item_name }}</TableCell>
                                            <TableCell class="text-right">
                                                {{ item.item_price.toLocaleString() }} 円
                                            </TableCell>
                                            <TableCell>{{ item.quantity }}</TableCell>
                                            <TableCell class="text-right">
                                                {{ item.subtotal.toLocaleString() }} 円
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 border-t pt-6 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-lg font-semibold">
                                合計: {{ Number(totalPrice).toLocaleString() }} 円
                            </div>

                            <div v-if="props.order[0].status == true" class="text-lg font-semibold">
                                未キャンセル
                            </div>
                            <div v-if="props.order[0].status == false" class="text-lg font-semibold">
                                キャンセル済み
                            </div>

                            <div v-if="props.order[0].status == false" class="text-lg font-semibold">
                               キャンセル日時: {{ dayjs(props.order[0].updated_at).format('YYYY/MM/DD HH:mm') }}
                            </div>

                            <div class="flex items-center gap-2">
                                <Link as="button" :href="route('purchases.edit', { purchase: props.order[0].id })" 
                                class="rounded-md bg-indigo-600 text-sm font-semibold text-white shadow-sm">
                                    編集する</Link>
                            </div>

                            <Link
                                :href="route('purchases.index')"
                                class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-200"
                            >
                                一覧に戻る
                            </Link>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
