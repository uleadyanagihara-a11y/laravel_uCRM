<script setup>
import { getToday } from '@/common'
import { onMounted, reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    customers: Array,
    items: Array,
    errors: {
        type: Object,
        default: () => ({})
    }
})

onMounted(() => {
    form.date = getToday()
    props.items.forEach(item => {
        itemList.value.push({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: 0
        })
    })
})

const itemList = ref([])

const form = reactive({
    date:null,
    customer_id: null,
    status: true,
    items: []
})

const totalPrice = computed(() => {
    let total = 0
    itemList.value.forEach(item => {
        total += item.price * item.quantity
    })
    return total
})

const storePurchase = () => {
    form.items = []
    itemList.value.forEach(item => {
        if( item.quantity > 0){
            form.items.push({
                id: item.id,
                quantity: Number(item.quantity)
            })
        }
    })
    router.post(route('purchases.store'), form)
}

const quantity = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
</script>

<template>
<form @submit.prevent="storePurchase">
<div v-if="$page.props.flash.message">
    {{ $page.props.flash.message }}
</div>
日付<br>
    <input type="date" name="date" v-model="form.date"><br>
    会員名<br>
    <select name="customer" v-model="form.customer_id">
        <option :value="null">選択してください</option>
        <option v-for="customer in customers" :value="customer.id" :key="customer.id">
            {{ customer.id }} : {{ customer.name }}
        </option>
    </select>
    <div v-if="props.errors.customer_id">{{ props.errors.customer_id }}</div>
    <br><br>
    商品・サービス<br>
    <table>
        <thead>
            <tr>
                <th>Id</th>
                <th>商品名</th>
                <th>金額</th>
                <th>数量</th>
                <th>小計</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in itemList" :key="item.id">
                <td>{{ item.id }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.price }}</td>
                <td>
                    <select name="quantity" v-model="item.quantity">
                        <option v-for="q in quantity" :value="q" :key="q">{{ q }}</option>
                    </select>
                </td>
                <td>{{ item.price * item.quantity }}</td>
            </tr>
        </tbody>
    </table>
<div v-if="props.errors.items">{{ props.errors.items }}</div>
<br>
合計: {{ totalPrice }} 円<br>
<button type="submit">登録する</button>
</form>
</template>
