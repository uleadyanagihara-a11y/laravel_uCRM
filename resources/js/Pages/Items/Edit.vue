<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const itemSchema = toTypedSchema(
    z.object({
        name: z.string().trim().min(1, '商品名は必須です'),
        memo: z.preprocess(
            (value) => value === '' ? null : value,
            z.string().nullable().optional()
        ),
        price: z.preprocess(
            (value) => value === '' || value === null ? undefined : Number(value),
            z.number({
                required_error: '商品価格は必須です',
                invalid_type_error: '商品価格は数値で入力してください',
            }).min(0, '商品価格は0円以上で入力してください')
        ),
        is_selling: z.number(),
    })
);

const {
    defineField,
    handleSubmit,
    errors: validationErrors,
} = useForm({
    validationSchema: itemSchema,
    initialValues: {
        name: props.item.name,
        memo: props.item.memo ?? '',
        price: props.item.price,
        is_selling: Number(props.item.is_selling),
    },
});

const [name] = defineField('name');
const [memo] = defineField('memo');
const [price] = defineField('price');
const [is_selling] = defineField('is_selling');

const updateItem = handleSubmit((values) => {
    router.put(route('items.update', props.item.id), values);
});
</script>

<template>
    <Head title="商品編集" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">商品編集</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="updateItem" class="w-full max-w-md space-y-6">
                            <fieldset class="space-y-4">
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            for="name"
                                            class="text-sm font-medium"
                                        >
                                            商品名
                                        </label>
                                        <Input
                                            id="name"
                                            v-model="name"
                                        />
                                        <InputError :message="validationErrors.name || props.errors?.name" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label
                                        for="memo"
                                        class="text-sm font-medium"
                                    >
                                        メモ
                                    </label>
                                    <Textarea
                                        id="memo"
                                        name="memo"
                                        v-model="memo"
                                        class="resize-none"
                                    />
                                    <InputError :message="validationErrors.memo || props.errors?.memo" />
                                </div>
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            for="price"
                                            class="text-sm font-medium"
                                        >
                                            商品価格
                                        </label>
                                        <Input
                                            type="number"
                                            id="price"
                                            v-model="price"
                                        />
                                        <InputError :message="validationErrors.price || props.errors?.price" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-sm font-medium">ステータス</p>
                                    <div class="flex items-center gap-6">
                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="radio"
                                                v-model="is_selling"
                                                :value="1"
                                            />
                                            販売中
                                        </label>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="radio"
                                                v-model="is_selling"
                                                :value="0"
                                            />
                                            停止中
                                        </label>
                                    </div>
                                    <InputError :message="validationErrors.is_selling || props.errors?.is_selling" />
                                </div>
                            </fieldset>

                            <div class="flex items-center gap-2">
                                <Button type="submit" class="rounded-md bg-indigo-600 text-sm font-semibold text-white shadow-sm">
                                    更新する</Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
