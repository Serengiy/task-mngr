<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Form, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { tasksStore } from '@/routes';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';

const props = defineProps({
    statuses: {
        type: Object,
        required: true,
    },
});

const errors = ref({
    name: '',
    description: '',
    due_date: '',
    responsible_id: '',
    status: '',
});

function submit() {
    router.post('/tasks', form.value, {
        onSuccess: (page) => {
            console.log('✅ Ответ получен:', page);
        },
        onError: (errs) => {
            errors.value = { ...errors.value, ...errs };
        }, });
}

const form = ref({
    name: 'asd',
    description: 'фыв',
    due_date: '2026-12-12',
    responsible_id: '1',
    status: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Задачи', href: '/' },
    { title: 'Создать задачу', href: '/tasks/create' },
];
</script>

<template>
    <Head title="Создание задачи" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- 🔹 Вся ширина экрана, с комфортными отступами -->
        <div class="w-full px-8 py-10">
            <h1 class="text-2xl font-semibold mb-8">Создание задачи</h1>

            <!-- 🔹 Адаптивная сетка -->
            <Form
                v-bind="tasksStore.form"
                class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full"
            >
                <!-- Название -->
                <div class="col-span-1">
                    <label class="block mb-1 font-medium">Название</label>
                    <input v-model="form.name" class="w-full border rounded-lg p-3" />
                    <InputError :message="errors.name" />
                </div>

                <!-- Статус -->
                <div class="col-span-1">
                    <label class="block mb-1 font-medium">Статус</label>
                    <select v-model="form.status" class="w-full border rounded-lg p-3">
                        <option value="">Выберите статус</option>
                        <option
                            v-for="(label, key) in props.statuses"
                            :key="key"
                            :value="key"
                        >
                            {{ label }}
                        </option>
                    </select>
                    <InputError :message="errors.status" />
                </div>

                <!-- Описание (на всю ширину) -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block mb-1 font-medium">Описание</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full border rounded-lg p-3"
                    />
                    <InputError :message="errors.description" />
                </div>

                <!-- Срок выполнения -->
                <div class="col-span-1">
                    <label class="block mb-1 font-medium">Срок выполнения</label>
                    <input
                        type="date"
                        v-model="form.due_date"
                        class="w-full border rounded-lg p-3"
                    />
                    <InputError :message="errors.due_date" />
                </div>

                <!-- ID ответственного -->
                <div class="col-span-1">
                    <label class="block mb-1 font-medium">ID ответственного</label>
                    <input
                        v-model="form.responsible_id"
                        class="w-full border rounded-lg p-3"
                    />
                    <InputError :message="errors.responsible_id" />
                </div>

                <!-- Кнопка -->
                <div class="md:col-span-2 flex justify-end mt-4">
                    <Button type="button" @click="submit">Создать</Button>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
