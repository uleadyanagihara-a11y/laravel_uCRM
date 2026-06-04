<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { ref } from 'vue';
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const customerSchema = toTypedSchema(
    z.object({
        name: z.string().trim().min(1, '顧客名は必須です'),
        kana: z.preprocess(
            (value) => value === '' ? null : value,
            z.string().nullable().optional()
        ),
        tel: z.preprocess(
            (value) => value === '' ? null : value,
            z.string().nullable().optional()
        ),
        email: z.preprocess(
            (value) => value === '' ? null : value,
            z.string().email('メールアドレスの形式で入力してください').nullable().optional()
        ),
        postcode: z.preprocess(
            (value) => value === '' ? null : value,
            z.string().nullable().optional()
        ),
        address: z.preprocess(
            (value) => value === '' ? null : value,
            z.string().nullable().optional()
        ),
        birthday: z.preprocess(
            (value) => value === '' ? null : value,
            z.string().nullable().optional()
        ),
        gender: z.preprocess(
            (value) => value === '' ? null : value,
            z.number().nullable().optional()
        ),
        memo: z.preprocess(
            (value) => value === '' ? null : value,
            z.string().nullable().optional()
        ),
    })
);

const {
    defineField,
    handleSubmit,
    errors: validationErrors,
} = useForm({
    validationSchema: customerSchema,
    initialValues: {
        name: '',
        kana: '',
        tel: '',
        email: '',
        postcode: '',
        address: '',
        birthday: '',
        gender: null,
        memo: '',
    },
});

const [name] = defineField('name');
const [kana] = defineField('kana');
const [tel] = defineField('tel');
const [email] = defineField('email');
const [postcode] = defineField('postcode');
const [address] = defineField('address');
const [birthday] = defineField('birthday');
const [gender] = defineField('gender');
const [memo] = defineField('memo');

const isSearchingAddress = ref(false);
const postcodeSearchError = ref('');

const normalizePostcode = (value) => String(value ?? '').replace(/\D/g, '');

const searchAddressByPostcode = async () => {
    postcodeSearchError.value = '';

    const normalizedPostcode = normalizePostcode(postcode.value);

    if (normalizedPostcode.length !== 7) {
        postcodeSearchError.value = '郵便番号は7桁で入力してください';
        return;
    }

    postcode.value = normalizedPostcode;
    isSearchingAddress.value = true;

    try {
        const response = await fetch(
            `https://zipcloud.ibsnet.co.jp/api/search?zipcode=${normalizedPostcode}`
        );
        const data = await response.json();

        if (data.status !== 200 || !data.results?.length) {
            postcodeSearchError.value = '住所が見つかりませんでした';
            return;
        }

        const result = data.results[0];
        address.value = `${result.address1}${result.address2}${result.address3}`;
    } catch (error) {
        postcodeSearchError.value = '住所検索に失敗しました';
    } finally {
        isSearchingAddress.value = false;
    }
};

const storeCustomer = handleSubmit((values) => {
    router.post(route('customers.store'), {
        ...values,
        postcode: normalizePostcode(values.postcode),
    });
});
</script>

<template>
    <Head title="顧客登録" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">顧客登録</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="storeCustomer" class="w-full max-w-md space-y-6">
                            <fieldset class="space-y-4">
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            type="text"
                                            for="name"
                                            class="text-sm font-medium"
                                        >
                                            顧客名
                                        </label>
                                        <Input
                                            type="text"
                                            id="name"
                                            v-model="name"
                                        />
                                        <InputError :message="validationErrors.name || props.errors?.name" />
                                    </div>                                   
                                    <div class="grid grid-cols-3 gap-4"></div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            type="text"
                                            for="kana"
                                            class="text-sm font-medium"
                                        >
                                            顧客名カナ
                                        </label>
                                        <Input
                                            type="text"
                                            id="kana"
                                            v-model="kana"
                                        />
                                        <InputError :message="validationErrors.kana || props.errors?.kana" />
                                    </div>                                   
                                    <div class="grid grid-cols-3 gap-4"></div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            type="tel"
                                            for="tel"
                                            class="text-sm font-medium"
                                        >
                                            電話番号
                                        </label>
                                        <Input
                                            type="tel"
                                            id="tel"
                                            v-model="tel"
                                        />
                                        <InputError :message="validationErrors.tel || props.errors?.tel" />
                                    </div>                                   
                                    <div class="grid grid-cols-3 gap-4"></div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            type="email"
                                            for="email"
                                            class="text-sm font-medium"
                                        >
                                            メールアドレス
                                        </label>
                                        <Input
                                            type="email"
                                            id="email"
                                            v-model="email"
                                        />
                                        <InputError :message="validationErrors.email || props.errors?.email" />
                                    </div>                                   
                                    <div class="grid grid-cols-3 gap-4"></div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            type="number"
                                            for="postcode"
                                            class="text-sm font-medium"
                                        >
                                            郵便番号
                                        </label>
                                        <Input
                                            type="text"
                                            id="postcode"
                                            v-model="postcode"
                                            inputmode="numeric"
                                            autocomplete="postal-code"
                                            placeholder="1234567"
                                            @input="postcodeSearchError = ''"
                                        />
                                        <div class="flex items-center gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                :disabled="isSearchingAddress"
                                                @click="searchAddressByPostcode"
                                            >
                                                {{ isSearchingAddress ? '検索中...' : '住所検索' }}
                                            </Button>
                                        </div>
                                        <InputError :message="postcodeSearchError || validationErrors.postcode || props.errors?.postcode" />
                                    </div>                                   
                                    <div class="grid grid-cols-3 gap-4"></div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            type="text"
                                            for="address"
                                            class="text-sm font-medium"
                                        >
                                            住所
                                        </label>
                                        <Input
                                            type="text"
                                            id="address"
                                            v-model="address"
                                        />
                                        <InputError :message="validationErrors.address || props.errors?.address" />
                                    </div>                                   
                                    <div class="grid grid-cols-3 gap-4"></div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label
                                            type="date"
                                            for="birthday"
                                            class="text-sm font-medium"
                                        >
                                            誕生日
                                        </label>
                                        <Input
                                            type="date"
                                            id="birthday"
                                            v-model="birthday"
                                        />
                                        <InputError :message="validationErrors.birthday || props.errors?.birthday" />
                                    </div>                                   
                                    <div class="grid grid-cols-3 gap-4"></div>
                                </div>

                                <div class="space-y-2">
                                    <p class="text-sm font-medium">性別</p>
                                    <div class="flex items-center gap-6">
                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="radio"
                                                v-model="gender"
                                                :value="0"
                                            />
                                            男性
                                        </label>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="radio"
                                                v-model="gender"
                                                :value="1"
                                            />
                                            女性
                                        </label>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="radio"
                                                v-model="gender"
                                                :value="2"
                                            />
                                            その他
                                        </label>
                                    </div>
                                    <InputError :message="validationErrors.gender || props.errors?.gender" />
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
                                 
                            </fieldset>

                            <div class="flex items-center gap-2">
                                <Button type="submit" class="rounded-md bg-indigo-600 text-sm font-semibold text-white shadow-sm">
                                    顧客登録</Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
