<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, onMounted} from 'vue'
import { getToday } from '@/common'
import BarChart from '@/Components/BarChart.vue'
import Table from '@/Components/ui/table/Table.vue'
import TableHeader from '@/Components/ui/table/TableHeader.vue'
import TableBody from '@/Components/ui/table/TableBody.vue'
import TableRow from '@/Components/ui/table/TableRow.vue'
import TableHead from '@/Components/ui/table/TableHead.vue'
import TableCell from '@/Components/ui/table/TableCell.vue'
import ResultTable from '@/Components/ResultTable.vue'

onMounted(() => {
    form.startDate = getToday()
    form.endDate = getToday()
})

const form = reactive({
    startDate: null,
    endDate: null,
    type: 'perDay',
    rfmPrms:[
        14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000
    ],
})
const data = reactive({})
const getData = async() => {
    try{
        await axios.get('/api/analysis/', {
            params:{
                startDate: form.startDate,
                endDate: form.endDate,
                type: form.type,
                rfmPrms: form.rfmPrms
            }
        })
        .then(res => {
            data.data = res.data.data
            data.labels = res.data.labels ?? []
            data.eachCount = res.data.eachCount ?? []
            data.totals = res.data.totals
            data.type = res.data.type
            console.log(res.data)
        })
    } catch (e){
        console.log(e.message)
    }
}
</script>

<template>
    <Head title="データ分析" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">データ分析</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="getData">
                            分析方法<br>
                            <input type="radio" v-model="form.type" value="perDay" checked>
                                <span class="mr-2">日別</span>
                            <input type="radio" v-model="form.type" value="perMonth">
                                <span class="mr-2">月別</span>
                            <input type="radio" v-model="form.type" value="perYear">
                                <span class="mr-2">年別</span>
                            <input type="radio" v-model="form.type" value="decile">
                                <span class="mr-2">デシル分析</span>
                            <input type="radio" v-model="form.type" value="rfm">
                            <span class="mr-2">RFM分析</span>
                            <br>
                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>
                            <div v-if="form.type === 'rfm'">
                                <table class="mx-auto">
                                <thead>
                                    <tr>
                                        <th>ランク</th>
                                        <th>R（〇日以内）</th>
                                        <th>F（〇回以上）</th>
                                        <th>M（〇円以上）</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>5</td>
                                        <td><input type="number" v-model="form.rfmPrms[0]"></td>
                                        <td><input type="number" v-model="form.rfmPrms[4]"></td>
                                        <td><input type="number" v-model="form.rfmPrms[8]"></td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td><input type="number" v-model="form.rfmPrms[1]"></td>
                                        <td><input type="number" v-model="form.rfmPrms[5]"></td>
                                        <td><input type="number" v-model="form.rfmPrms[9]"></td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td><input type="number" v-model="form.rfmPrms[2]"></td>
                                        <td><input type="number" v-model="form.rfmPrms[6]"></td>
                                        <td><input type="number" v-model="form.rfmPrms[10]"></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><input type="number" v-model="form.rfmPrms[3]"></td>
                                        <td><input type="number" v-model="form.rfmPrms[7]"></td>
                                        <td><input type="number" v-model="form.rfmPrms[11]"></td>
                                    </tr>
                                </tbody>
                                </table>
                            </div>
                            <button type="submit" class="rounded-md bg-indigo-600
                            text-sm font-semibold text-white shadow-sm">
                                分析する
                            </button>
                        </form>
                        <BarChart
                            v-if="data.data && data.type !== 'decile' && data.type !== 'rfm'"
                            :data="data"
                        />
                        <ResultTable :data="data" />
                            <Table
                                v-if="data.data && data.type !== 'decile' && data.type !== 'rfm'"
                            >
                                <TableHeader>
                                    <TableRow>
                                    <TableHead class="w-[100px]">年月日</TableHead>
                                    <TableHead>金額</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow 
                                        v-for="item in data.data"
                                        :key="item.date"
                                    >                                       
                                        <TableCell>{{item.date}}</TableCell>
                                        <TableCell>{{item.total}}</TableCell>                                        
                                    </TableRow>                                
                                </TableBody>
                            </Table>                                                         
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
