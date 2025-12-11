<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
	department_name: {
		type: String,
		default: ''
	}
});

const form = useForm({
	department_name: props.department_name || '',
});

const submit = () => {
	form.post(route('departments.store'), {
		onFinish: () => form.reset('department_name'),
	});
};
</script>

<template>
	<CreateLayout
		title="Create Department"
		subtitle="Add a new department to organize your teams"
		:breadcrumb-items="[
			{ label: 'Home', href: route('dashboard') },
			{ label: 'Departments', href: route('departments.index') },
			{ label: 'Create' }
		]"
		icon="plus"
		max-width="2xl"
	>
		<form @submit.prevent="submit">
			<div>
				<InputLabel for="department_name" value="Department Name" />
				<TextInput
					id="department_name"
					type="text"
					class="mt-1 block w-full"
					v-model="form.department_name"
					required
					autofocus
				/>
				<InputError class="mt-2" :message="form.errors.department_name" />
			</div>

			<div class="flex items-center justify-end mt-6">
				<Link :href="route('departments.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
					Cancel
				</Link>
				<PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
					Create Department
				</PrimaryButton>
			</div>
		</form>
	</CreateLayout>
</template>