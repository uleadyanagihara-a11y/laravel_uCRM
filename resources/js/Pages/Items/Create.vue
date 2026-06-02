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
    })
);

const {
    defineField,
    handleSubmit,
    errors: validationErrors,
} = useForm({
    validationSchema: itemSchema,
    initialValues: {
        name: '',
        memo: '',
        price: '',
    },
});

const [name] = defineField('name');
const [memo] = defineField('memo');
const [price] = defineField('price');

const storeItem = handleSubmit((values) => {
    router.post(route('items.store'), values);
});
</script>

<template>
    <Head title="商品登録" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">商品登録</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="storeItem" class="w-full max-w-md space-y-6">
                            <fieldset class="space-y-4">
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            type="text"
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
                                    <div class="grid grid-cols-3 gap-4"></div>
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
                                    <div class="grid grid-cols-3 gap-4"></div>
                                </div>
                            </fieldset>

                            <div class="flex items-center gap-2">
                                <Button type="submit" class="rounded-md bg-indigo-600 text-sm font-semibold text-white shadow-sm">
                                    商品登録</Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
