<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import FlashMessage from '@/Components/FlashMessage.vue';

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
                        <FlashMessage />
                        <div class="mb-4 flex justify-end">
                            <Link as="button" :href="route('items.create')" type="button" 
                            class="rounded-md bg-indigo-600 px-4 py-2 
                            text-sm font-semibold text-white shadow-sm hover:bg-indigo-700
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">商品登録</Link>
                                                 
                        </div>                                     
                        <Table>
                            <TableHeader>
                                <TableRow>
                                <TableHead class="w-[100px]">Id</TableHead>
                                <TableHead>商品名</TableHead>
                                <TableHead>価格</TableHead>
                                <TableHead>ステータス</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="item in items"
                                    :key="item.id"
                                >
                                    <TableCell class="font-medium">
                                        <Link class="text-blue-400" :href="route('items.show',{ item: item.id })">
                                            {{item.id}}
                                        </Link>
                                    </TableCell>
                                    <TableCell>{{item.name}}</TableCell>
                                    <TableCell>{{item.price}}</TableCell>
                                    <TableCell>
                                        <span v-if="item.is_selling === 1">販売中</span>
                                        <span v-if="item.is_selling === 0">停止中</span>
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
