<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { tasksCreate } from '@/routes';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const auth = page.props.auth;

const props = defineProps({
    tasks: Object,
    statuses: Object,
});

const filters = ref({
    status: '',
    per_page: props.tasks.meta.per_page || 15,
    page: props.tasks.meta.current_page || 1,
});

watch(filters, () => {
    router.get('/', filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, { deep: true });

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Задачи', href: '/' },
];
</script>

<template>
    <Head title="Задачи" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- 🔹 Заголовок -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold">Список задач</h1>
                <Link
                    :href="tasksCreate()"
                    class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Добавить задачу
                </Link>
            </div>

            <!-- 🔹 Фильтры -->
            <div class="flex flex-wrap items-center gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Статус</label>
                    <select
                        v-model="filters.status"
                        class="border rounded-lg p-2 w-48"
                    >
                        <option value="">Все</option>
                        <option
                            v-for="(label, key) in props.statuses"
                            :key="key"
                            :value="key"
                        >
                            {{ label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- 🔹 Таблица -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left">Название</th>
                        <th class="px-4 py-2 text-left">Ответственный</th>
                        <th class="px-4 py-2 text-left">Срок</th>
                        <th class="px-4 py-2 text-left">Статус</th>
                        <th class="px-4 py-2 text-left">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="task in props.tasks.data"
                        :key="task.id"
                        class="hover:bg-gray-100 dark:hover:bg-gray-900"
                    >
                        <td class="px-4 py-2">{{ task.name }}</td>
                        <td class="px-4 py-2">{{ task.responsible?.name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ task.due_date }}</td>
                        <td class="px-4 py-2">{{ task.status }}</td>
                        <td class="px-4 py-2 space-x-2">
                            <Link
                                :href="`/tasks/${task.id}`"
                                class="text-blue-500 hover:underline"
                            >
                                Открыть
                            </Link>
                            <Link
                                v-if="task.creator_id === auth.user.id"
                                :href="`/tasks/${task.id}/edit`"
                                class="text-green-500 hover:underline"
                            >
                                Редактировать
                            </Link>
                            <Link
                                v-if="task.creator_id === auth.user.id"
                                as="button"
                                method="delete"
                                :href="`/tasks/${task.id}`"
                                class="text-red-500 hover:underline"
                            >
                                Удалить
                            </Link>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- 🔹 Пагинация -->
            <div class="flex justify-center items-center gap-2 mt-6">
                <button
                    v-if="props.tasks.links.prev"
                    @click="router.get(props.tasks.links.prev, filters.value)"
                    class="px-3 py-1 border rounded hover:bg-gray-100"
                >
                    Назад
                </button>

                <span class="text-sm">
                    Страница {{ props.tasks.meta.current_page }} из
                    {{ props.tasks.meta.last_page }}
                </span>

                <button
                    v-if="props.tasks.links.next"
                    @click="router.get(props.tasks.links.next, filters.value)"
                    class="px-3 py-1 border rounded hover:bg-gray-100"
                >
                    Вперёд
                </button>
            </div>
        </div>
    </AppLayout>
</template>
