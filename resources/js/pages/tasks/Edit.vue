<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Form, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { tasksStore } from '@/routes';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { computed } from 'vue';

const props = defineProps({
    statuses: {
        type: Object,
        required: true,
    },
    task: {
        type: Object,
        required: true,
    },
    users: {
        type: Object,
        required: true,
    }
});

const errors = ref({
    name: '',
    description: '',
    due_date: '',
    responsible_id: '',
    status: '',
    participants: '',
});

// заполняем форму начальными данными
const form = ref({
    name: props.task.name ?? '',
    description: props.task.description ?? '',
    due_date: props.task.due_date ?? '',
    responsible_id: props.task.responsible?.id ?? '',
    status: Object.keys(props.statuses).find(
        key => props.statuses[key] === props.task.status
    ) ?? '',
    participants: props.task.participants?.map(p => p.id) ?? [],
});

function submit() {
    router.put(`/tasks/${props.task.id}`, form.value, {
        onSuccess: (page) => {
            console.log('✅ Задача обновлена:', page);
        },
        onError: (errs) => {
            errors.value = { ...errors.value, ...errs };
        },
    });
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Задачи', href: '/' },
    { title: 'Редактировать задачу', href: `/tasks/${props.task.id}/edit` },
];


const selectedUsers = computed(() =>
    props.users.filter(u => form.value.participants.includes(u.id))
);

const availableUsers = computed(() =>
    props.users.filter(u => !form.value.participants.includes(u.id))
);

function addParticipant(userId: number) {
    if (!form.value.participants.includes(userId)) {
        form.value.participants.push(userId);
    }
}

function removeParticipant(userId: number) {
    form.value.participants = form.value.participants.filter(id => id !== userId);
}

</script>

<template>
    <Head title="Редактирование задачи" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- 🔹 Вся ширина экрана -->
        <div class="w-full px-8 py-10">
            <h1 class="text-2xl font-semibold mb-8">Редактирование задачи</h1>
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

                <!-- Описание -->
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

                <!-- Ответственный -->
                <div class="col-span-1">
                    <label class="block mb-1 font-medium">Ответственный</label>
                    <select
                        v-model="form.responsible_id"
                        class="w-full border rounded-lg p-3"
                    >
                        <option value="">Выберите ответственного</option>
                        <option
                            v-for="user in props.users"
                            :key="user.id"
                            :value="user.id"
                        >
                            {{ user.name }}
                        </option>
                    </select>
                    <InputError :message="errors.responsible_id" />
                </div>

                <!-- Участники -->
                <!-- Участники -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block mb-2 font-medium text-lg">Наблюдатели</label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Левая колонка — возможные наблюдатели -->
                        <div>
                            <h3 class="font-semibold mb-2">Возможные наблюдатели</h3>
                            <div class="border rounded-lg p-3 h-64 overflow-y-auto">
                                <div
                                    v-for="user in availableUsers"
                                    :key="user.id"
                                    class="flex items-center justify-between py-1 border-b last:border-none"
                                >
                                    <span>{{ user.name }} ({{ user.email }})</span>
                                    <button
                                        type="button"
                                        class="text-green-600 hover:text-green-800"
                                        @click="addParticipant(user.id)"
                                    >
                                        ➕
                                    </button>
                                </div>
                                <p v-if="availableUsers.length === 0" class="text-sm text-gray-500">
                                    Все пользователи уже наблюдатели.
                                </p>
                            </div>
                        </div>

                        <!-- Правая колонка — текущие наблюдатели -->
                        <div>
                            <h3 class="font-semibold mb-2">Наблюдатели</h3>
                            <div class="border rounded-lg p-3 h-64 overflow-y-auto">
                                <div
                                    v-for="user in selectedUsers"
                                    :key="user.id"
                                    class="flex items-center justify-between py-1 border-b last:border-none"
                                >
                                    <span>{{ user.name }} ({{ user.email }})</span>
                                    <button
                                        type="button"
                                        class="text-red-600 hover:text-red-800"
                                        @click="removeParticipant(user.id)"
                                    >
                                        ❌
                                    </button>
                                </div>
                                <p v-if="selectedUsers.length === 0" class="text-sm text-gray-500">
                                    Нет наблюдателей.
                                </p>
                            </div>
                        </div>
                    </div>

                    <InputError :message="errors.participants" />
                </div>


                <!-- Кнопка -->
                <div class="md:col-span-2 flex justify-end mt-4">
                    <Button type="button" @click="submit">Сохранить</Button>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>


