<script setup>
import { Input } from '@/Components/ui/input';
import axios from 'axios';
import MicroModal from 'micromodal';
import { onMounted, ref } from 'vue';

const props = defineProps({
    modalId: {
        type: String,
        default: 'customer-search-modal',
    },
    title: {
        type: String,
        default: '会員検索',
    },
    triggerText: {
        type: String,
        default: '検索',
    },
});

const emit = defineEmits(['customer-selected']);

const search = ref('');
const customers = ref([]);
const isSearching = ref(false);
const searchError = ref('');
const hasSearched = ref(false);

const searchCustomers = async () => {
    searchError.value = '';
    isSearching.value = true;
    hasSearched.value = true;

    try {
        const response = await axios.get(route('customers.search'), {
            params: {
                search: search.value,
            },
        });

        customers.value = response.data.data;
    } catch (error) {
        customers.value = [];
        searchError.value = '検索に失敗しました。';
        console.error(error);
    } finally {
        isSearching.value = false;
    }
};

const selectCustomer = (customer) => {
    emit('customer-selected', customer);
    MicroModal.close(props.modalId);
};

onMounted(async () => {
    MicroModal.init({
        disableScroll: true,
        awaitOpenAnimation: true,
        awaitCloseAnimation: true,
    });
});

</script>

<template>
    <div class="modal micromodal-slide" :id="props.modalId" aria-hidden="true">
        <div class="modal__overlay" tabindex="-1" data-micromodal-close>
            <div class="modal__container" role="dialog" aria-modal="true" :aria-labelledby="`${props.modalId}-title`">
                <header class="modal__header">
                    <h2 class="modal__title" :id="`${props.modalId}-title`">
                        {{ props.title }}
                    </h2>
                    <button type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                </header>
                <main class="modal__content" :id="`${props.modalId}-content`">
                    <div class="space-y-4">
                        <div class="flex gap-2">
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="会員名・フリガナ・電話番号で検索"
                                @keyup.enter="searchCustomers"
                            />
                            <button
                                type="button"
                                class="modal__btn modal__btn-primary whitespace-nowrap"
                                :disabled="isSearching"
                                @click="searchCustomers"
                            >
                                {{ isSearching ? '検索中' : '検索する' }}
                            </button>
                        </div>

                        <p v-if="searchError" class="text-sm text-red-600">
                            {{ searchError }}
                        </p>

                        <div v-if="customers.length" class="overflow-x-auto rounded-md border">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-gray-50 text-left">
                                        <th class="px-3 py-2">Id</th>
                                        <th class="px-3 py-2">氏名</th>
                                        <th class="px-3 py-2">カナ</th>
                                        <th class="px-3 py-2">電話番号</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="customer in customers"
                                        :key="customer.id"
                                        class="border-b last:border-b-0"
                                    >
                                        <td class="px-3 py-2">{{ customer.id }}</td>
                                        <td class="px-3 py-2">{{ customer.name }}</td>
                                        <td class="px-3 py-2">{{ customer.kana }}</td>
                                        <td class="px-3 py-2">{{ customer.tel }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <button
                                                type="button"
                                                class="modal__btn modal__btn-primary"
                                                @click="selectCustomer(customer)"
                                            >
                                                選択
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p v-else-if="hasSearched && !isSearching" class="text-sm text-gray-500">
                            検索結果はありません。
                        </p>

                        <p v-else class="text-sm text-gray-500">
                            会員名・フリガナ・電話番号で検索できます。
                        </p>
                    </div>
                </main>
                <footer class="modal__footer">
                    <button type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">
                        閉じる
                    </button>
                </footer>
            </div>
        </div>
    </div>

    <button type="button" class="modal__btn modal__btn-primary" :data-micromodal-trigger="props.modalId">
        {{ props.triggerText }}
    </button>
</template>
