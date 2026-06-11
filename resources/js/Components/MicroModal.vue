<script setup>
import { Input } from '@/Components/ui/input';
import MicroModal from 'micromodal';
import { onMounted } from 'vue';

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

onMounted(() => {
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
                        <Input
                            type="text"
                            placeholder="会員名・フリガナで検索"
                        />
                        <p class="text-sm text-gray-500">
                            検索結果の表示領域です。
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
