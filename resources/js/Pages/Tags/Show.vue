<template>
    <ShowLayout
        :title="tag.name"
        subtitle="Tag Information"
        icon="tag"
        :actions="userPermissions && (userPermissions.includes('edit_tags') || userPermissions.includes('delete_tags'))"
    >
        <template #actions>
            <Link v-if="userPermissions.includes('edit_tags')" :href="route('tags.edit', tag.id)" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-4M11 4l10 10M11 4v6h6"/>
                </svg>
                Edit
            </Link>
            <DangerButton v-if="userPermissions.includes('delete_tags')" @click="confirmTagDeletion" class="bg-white/20 text-white hover:bg-white/30 rounded-xl px-6 py-3">
                Delete
            </DangerButton>
        </template>

        <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-400">
            <div class="p-6 text-gray-900">
                <div class="flex items-center mb-6 pb-4 border-b">
                    <div class="bg-gradient-to-br from-indigo-500 to-blue-600 p-3 rounded-xl mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-gray-800">Tag Details</h2>
                        <p class="text-sm text-gray-500">ID: #{{ tag.id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center mb-4">
                            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-2 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Tag Information</h3>
                        </div>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ tag.id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tag Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ tag.name }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

                <Modal :show="confirmingTagDeletion" @close="closeModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900"> 
                            Are you sure you want to delete this tag?
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            This action cannot be undone. This will permanently delete the tag and all its related data.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                            <DangerButton
                                class="ms-3"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                @click="deleteTag"
                            >
                                Delete Tag
                            </DangerButton>
                        </div>
                    </div>
                </Modal>
    </ShowLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ShowLayout from '@/Components/ShowLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    tag: {
        type: Object,
        required: true
    }
});

const confirmingTagDeletion = ref(false);
const form = useForm({});
const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const confirmTagDeletion = () => {
    confirmingTagDeletion.value = true;
};

const deleteTag = () => {
    form.delete(route('tags.destroy', props.tag.id), {
        onFinish: () => {
            confirmingTagDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingTagDeletion.value = false;
};

</script>