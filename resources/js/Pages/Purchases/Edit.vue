<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { computed, onMounted, reactive, ref } from 'vue';
import dayjs from 'dayjs'

const props = defineProps({
    order: Array,
    items: Array,
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const itemList = ref([]);

const form = reactive({
    id: props.order[0].id,
    date: dayjs(props.order[0].created_at).format("YYYY-MM-DD"),
    customer_id: props.order[0].customer_id,
    status: props.order[0].status,
    items: [],
});

const quantity = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

onMounted(() => {
    itemList.value = props.items.map((item) => ({
        id: item.id,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
    }));
});

const totalPrice = computed(() => {
    return itemList.value.reduce((total, item) => {
        return total + item.price * item.quantity;
    }, 0);
});

const updatePurchase = id => {
    form.items = itemList.value
        .filter((item) => item.quantity > 0)
        .map((item) => ({
            id: item.id,
            quantity: Number(item.quantity),
        }));

    router.put(route('purchases.update', { purchase: id }), form);
};

const selectedCustomer = ref(null);

</script>

<template>
    <Head title="購買履歴　編集画面" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">購買履歴　編集画面</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="updatePurchase(form.id)" class="space-y-8">
                            <div v-if="$page.props.flash.message" class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                                {{ $page.props.flash.message }}
                            </div>

                            <fieldset class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label for="date" class="text-sm font-medium">
                                        日付
                                    </label>
                                    <Input disabled
                                        id="date"
                                        type="date"
                                        name="date"
                                        :model-value="form.date"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <div class="space-y-2">
                                        <label for="customer" class="text-sm font-medium">
                                            顧客名
                                        </label>
                                         <Input disabled
                                        id="customer"
                                        type="text"
                                        name="customer"
                                        :model-value="props.order[0].customer_name"
                                        />
                                    </div>
                                    <div v-if="selectedCustomer" class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                                        {{ selectedCustomer.name }}（{{ selectedCustomer.kana }}）
                                    </div>
                                    <InputError :message="props.errors.customer_id" />
                                </div>
                            </fieldset>

                            <div class="flex justify-end gap-2">
                                
                            </div>

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
                                                v-for="item in itemList"
                                                :key="item.id"
                                            >
                                                <TableCell class="font-medium">
                                                    {{ item.id }}
                                                </TableCell>
                                                <TableCell>{{ item.name }}</TableCell>
                                                <TableCell class="text-right">
                                                    {{ item.price.toLocaleString() }} 円
                                                </TableCell>
                                                <TableCell>
                                                    <select
                                                        name="quantity"
                                                        v-model="item.quantity"
                                                        class="h-9 w-20 rounded-md border border-input bg-background px-3 py-1 text-sm"
                                                    >
                                                        <option v-for="q in quantity" :key="q" :value="q">
                                                            {{ q }}
                                                        </option>
                                                    </select>
                                                </TableCell>
                                                <TableCell class="text-right">
                                                    {{ (item.price * item.quantity).toLocaleString() }} 円
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>

                                <InputError :message="props.errors.items" />
                            </div>

                            <div class="border-t pt-6 space-y-4">
                                <div class="text-lg font-semibold">
                                    合計: {{ totalPrice.toLocaleString() }} 円
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">ステータス</label>
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-1 text-sm">
                                            <input type="radio" v-model="form.status" :value="1" />
                                            未キャンセル
                                        </label>
                                        <label class="flex items-center gap-1 text-sm">
                                            <input type="radio" v-model="form.status" :value="0" />
                                            キャンセルする
                                        </label>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <Button type="submit" class="rounded-md bg-indigo-600 text-sm font-semibold
                                     text-white shadow-sm">
                                        更新する
                                    </Button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
