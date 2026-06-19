<script setup>
import Table from '@/Components/ui/table/Table.vue'
import TableHeader from '@/Components/ui/table/TableHeader.vue'
import TableBody from '@/Components/ui/table/TableBody.vue'
import TableRow from '@/Components/ui/table/TableRow.vue'
import TableHead from '@/Components/ui/table/TableHead.vue'
import TableCell from '@/Components/ui/table/TableCell.vue'

const props = defineProps({
    'data' : Object
})
</script>
<template>
    <div>
        <Table v-if="data.type === 'decile'">
            <TableHeader>
                <TableRow>
                <TableHead class="w-[100px]">グループ</TableHead>
                <TableHead>平均</TableHead>
                <TableHead>合計金額</TableHead>
                <TableHead>構成比</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow 
                    v-for="item in data.data"
                    :key="item.date"
                >                                       
                    <TableCell>{{item.decile}}</TableCell>
                    <TableCell>{{item.average}}</TableCell>
                    <TableCell>{{item.totalPerGroup}}</TableCell>
                    <TableCell>{{item.totalRatio}}</TableCell>                              
                </TableRow>                                
            </TableBody>
        </Table>
    </div>
</template>