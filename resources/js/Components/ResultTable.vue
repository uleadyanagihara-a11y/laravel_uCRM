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

        <div v-if="data.type === 'rfm'" class="mt-6 space-y-6">
            <section>
                <p class="mb-2 text-sm text-gray-700">
                    合計人数
                    <span class="font-semibold">{{ data.totals ?? 0 }}</span>
                    人 / RFMランク毎の人数
                </p>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[100px]">Rank</TableHead>
                            <TableHead>R</TableHead>
                            <TableHead>F</TableHead>
                            <TableHead>M</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="rfm in data.eachCount"
                            :key="rfm.rank"
                        >
                            <TableCell class="font-medium">{{ rfm.rank }}</TableCell>
                            <TableCell>{{ rfm.r }}</TableCell>
                            <TableCell>{{ rfm.f }}</TableCell>
                            <TableCell>{{ rfm.m }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </section>

            <section>
                <p class="mb-2 text-sm text-gray-700">RとFの集計表</p>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>R \ F</TableHead>
                            <TableHead>5</TableHead>
                            <TableHead>4</TableHead>
                            <TableHead>3</TableHead>
                            <TableHead>2</TableHead>
                            <TableHead>1</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="rf in data.data"
                            :key="rf.rRank"
                        >
                            <TableCell class="font-medium">{{ rf.rRank }}</TableCell>
                            <TableCell>{{ rf.f_5 }}</TableCell>
                            <TableCell>{{ rf.f_4 }}</TableCell>
                            <TableCell>{{ rf.f_3 }}</TableCell>
                            <TableCell>{{ rf.f_2 }}</TableCell>
                            <TableCell>{{ rf.f_1 }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </section>
        </div>
    </div>
</template>
