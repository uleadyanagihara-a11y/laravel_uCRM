<script setup>
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
import { ref } from 'vue'

defineProps({
    customers:  Object
});

const search = ref('')

//refの値を取得するには.valueが必要
const searchCustomers = () => {
    router.get(route('customers.index'), { search: search.value })
}
</script>

<template>
    <Head title="顧客一覧" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">顧客一覧</h2>
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
                            <Link as="button" :href="route('customers.create')" type="button" 
                            class="rounded-md bg-indigo-600 px-4 py-2 
                            text-sm font-semibold text-white shadow-sm hover:bg-indigo-700
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">顧客登録</Link>
                                                 
                        </div>                                     
                        <Table>
                            <TableHeader>
                                <TableRow>
                                <TableHead class="w-[100px]">Id</TableHead>
                                <TableHead>氏名</TableHead>
                                <TableHead>カナ</TableHead>
                                <TableHead>電話番号</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="customer in customers.data"
                                    :key="customer.id"
                                >
                                    <TableCell class="font-medium">
                                        {{customer.id}}
                                    </TableCell>
                                    <TableCell>{{customer.name}}</TableCell>
                                    <TableCell>{{customer.kana}}</TableCell>
                                    <TableCell>{{customer.tel}}</TableCell>
                                </TableRow>                                
                            </TableBody>
                        </Table>                                                         
                    </div>
                </div>
                <Pagination class="mt-6" :links="customers.links"></Pagination>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
