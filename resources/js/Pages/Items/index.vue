<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="商品一覧" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">商品一覧</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <Table>
                            <TableCaption>登録済み商品の一覧です。</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>商品名</TableHead>
                                    <TableHead>メモ</TableHead>
                                    <TableHead class="text-right">価格</TableHead>
                                    <TableHead>販売状態</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="item in items" :key="item.id">
                                    <TableCell class="font-medium">
                                        {{ item.name }}
                                    </TableCell>
                                    <TableCell class="text-muted-foreground">
                                        {{ item.memo ?? '-' }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ Number(item.price).toLocaleString() }}円
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="item.is_selling ? 'bg-green-100 text-green-800' : 'bg-muted text-muted-foreground'"
                                        >
                                            {{ item.is_selling ? '販売中' : '停止中' }}
                                        </span>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="items.length === 0">
                                    <TableCell colspan="4" class="h-24 text-center text-muted-foreground">
                                        商品が登録されていません。
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
