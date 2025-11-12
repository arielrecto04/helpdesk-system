<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// 1. Tanggapin ang 'team' object bilang prop mula sa controller
const props = defineProps({
    team: Object,
});

// 2. I-define ang form object gamit ang data mula sa 'team' prop
const form = useForm({
    team_name: props.team.team_name,
});

// 3. Gumawa ng function para sa pag-update
const submit = () => {
    // Gamitin ang 'put' method para sa pag-update
    // Siguraduhin na ang route name ay 'helpdeskteams.update' (base sa Route::resource)
    // at ipasa ang ID ng team
    form.put(route('helpdeskteams.update', props.team.id));
};
</script>

<template>
    <!-- 4. Palitan ang title -->
    <Head :title="'Edit Team: ' + team.team_name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <!-- 5. Palitan ang header -->
                Edit Team: <span class="font-bold">{{ team.team_name }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- 6. I-bind ang 'submit' event sa 'submit' function -->
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="team_name" value="Team Name" />
                                <TextInput
                                    id="team_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.team_name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.team_name" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <!-- 7. Itama ang 'Cancel' link route -->
                                <Link :href="route('helpdeskteams.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>

                                <!-- 8. Palitan ang text ng button at siguraduhin na may 'type=submit' -->
                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Team
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>