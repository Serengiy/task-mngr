<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { type TaskResource } from '@/types'
import { ref } from 'vue'

const props = defineProps<{
    task: TaskResource
    user: { id: number; name: string } // текущий авторизованный пользователь
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Задачи', href: '/' },
    { title: props.task.name, href: `/tasks/${props.task.id}` },
]

const comments = ref([...props.task.comments])

// --- Новый комментарий ---
const newComment = ref('')
const isSubmitting = ref(false)

// --- Редактирование комментариев ---
const editingCommentId = ref<number | null>(null)
const editingText = ref('')

// --- Методы ---
const submitComment = async () => {
    if (!newComment.value.trim()) return alert('Введите текст комментария')
    isSubmitting.value = true

    try {
        const response = await fetch(`/tasks/${props.task.id}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.Laravel.csrfToken,
            },
            body: JSON.stringify({ text: newComment.value }),
        })

        if (!response.ok) throw new Error('Ошибка при добавлении комментария')

        const data = await response.json()
        comments.value.unshift(data.data)
        newComment.value = ''
    } catch (err) {
        console.error(err)
        alert('Не удалось добавить комментарий')
    } finally {
        isSubmitting.value = false
    }
}


const startEditing = (comment: any) => {
    editingCommentId.value = comment.id
    editingText.value = comment.text
}

const cancelEditing = () => {
    editingCommentId.value = null
    editingText.value = ''
}

const updateComment = async (commentId: number) => {
    if (!editingText.value.trim()) return alert('Комментарий не может быть пустым')

    try {
        const response = await fetch(`/comments/${commentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.Laravel.csrfToken,
            },
            body: JSON.stringify({ comment_id: commentId, text: editingText.value }),
        })

        if (!response.ok) throw new Error('Ошибка при обновлении комментария')

        const data = await response.json()
        const index = comments.value.findIndex(c => c.id === commentId)
        if (index !== -1) comments.value[index] = data.data

        cancelEditing()
    } catch (err) {
        console.error(err)
        alert('Не удалось обновить комментарий')
    }
}

const deleteComment = async (commentId: number) => {
    if (!confirm('Удалить комментарий?')) return

    try {
        const response = await fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken,
            },
        })

        if (!response.ok) throw new Error('Ошибка при удалении комментария')

        comments.value = comments.value.filter(c => c.id !== commentId)
    } catch (err) {
        console.error(err)
        alert('Не удалось удалить комментарий')
    }
}

</script>

<template>
    <Head :title="props.task?.name || 'Задача'" />

    <AppLayout v-if="props.task" :breadcrumbs="breadcrumbs">
        <div class="w-full px-8 py-10 space-y-6">
            <h1 class="text-2xl font-semibold">{{ props.task.name }}</h1>

            <!-- Основные данные -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <p><strong>Статус:</strong> {{ props.task.status }}</p>
                    <p><strong>Создано:</strong> {{ props.task.created_at }}</p>
                    <p><strong>Срок выполнения:</strong> {{ props.task.due_date }}</p>
                </div>
                <div>
                    <p><strong>Создатель:</strong> {{ props.task.creator.name }}</p>
                    <p><strong>Ответственный:</strong> {{ props.task.responsible.name }}</p>
                </div>
            </div>

            <!-- Описание -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-2">Описание</h2>
                <p class="whitespace-pre-line">{{ props.task.description }}</p>
            </div>

            <!-- Участники -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-2">Участники</h2>
                <ul>
                    <li v-for="(participant, index) in props.task.participants" :key="participant.id">
                        {{ index + 1 }}) {{ participant.name }}
                    </li>
                </ul>
            </div>

            <!-- Комментарии -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Комментарии</h2>

                <!-- Форма нового комментария -->
                <div class="mb-6">
                    <textarea
                        v-model="newComment"
                        rows="3"
                        placeholder="Напишите комментарий..."
                        class="w-full border rounded-lg p-3 mb-2"
                    ></textarea>
                    <button
                        @click="submitComment"
                        :disabled="isSubmitting"
                        class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Сохраняем...' : 'Добавить комментарий' }}
                    </button>
                </div>

                <!-- Список комментариев -->
                <ul>
                    <li
                        v-for="comment in comments"
                        :key="comment.id"
                        class="border rounded-lg p-3 mb-2"
                    >
                        <div v-if="editingCommentId === comment.id">
                            <textarea
                                v-model="editingText"
                                class="w-full border rounded p-2"
                            ></textarea>
                            <div class="mt-1">
                                <button
                                    @click="updateComment(comment.id)"
                                    class="px-2 py-1 bg-green-500 text-white rounded mr-2"
                                >
                                    Сохранить
                                </button>
                                <button
                                    @click="cancelEditing"
                                    class="px-2 py-1 bg-gray-300 rounded"
                                >
                                    Отмена
                                </button>
                            </div>
                        </div>
                        <div v-else>
                            <p>{{ comment.text }}</p>
                            <p class="text-sm text-gray-500">
                                — {{ comment.user.name }}, {{ comment.created_at }}
                            </p>

                            <div
                                v-if="comment.user.id === props.user.id"
                                class="mt-2 flex gap-2"
                            >
                                <button
                                    @click="startEditing(comment)"
                                    class="px-2 py-1 bg-yellow-400 text-white rounded"
                                >
                                    Редактировать
                                </button>
                                <button
                                    @click="deleteComment(comment.id)"
                                    class="px-2 py-1 bg-red-500 text-white rounded"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

