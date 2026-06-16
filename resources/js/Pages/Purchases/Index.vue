<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    orders: Object
})

onMounted(() => {
    console.log(props.orders.data)
})

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
import Pagination from '@/Components/Pagination.vue'
import dayjs from 'dayjs'


const search = ref('')
</script>

<template>
    <Head title="購買履歴" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">購買履歴</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <FlashMessage />
                        <div class="mb-4 flex justify-end">
                            <div>
                                <input type="text" name="search" v-model="search">
                                <button class="bg-blue-300 text-white py-2 px-2" 
                                @click="searchCustomers">検索</button>
                            </div>
                                          
                        </div>                                     
                        <Table>
                            <TableHeader>
                                <TableRow>
                                <TableHead class="w-[100px]">Id</TableHead>
                                <TableHead>氏名</TableHead>
                                <TableHead>合計金額</TableHead>
                                <TableHead>ステータス</TableHead>
                                <TableHead>購入日</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="order in props.orders.data"
                                    :key="order.id"
                                >
                                    <TableCell class="font-medium">
                                         <Link class="text-blue-400" :href="route('purchases.show',{ purchase: order.id })">
                                            {{order.id}}</Link>
                                    </TableCell>
                                    <TableCell>{{order.customer_name}}</TableCell>
                                    <TableCell>{{order.total}}</TableCell>
                                    <TableCell>{{order.status}}</TableCell>
                                    <TableCell>{{dayjs(order.created_at).format('YYYY-MM-DD HH:mm:ss')}}</TableCell>
                                </TableRow>                                
                            </TableBody>
                        </Table>                                                         
                    </div>
                </div>
                <Pagination class="mt-6" :links="props.orders.links"></Pagination>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
