# Paano Gawing Iisa ang Design ng lahat ng Create.vue

Ginawa natin ang **`CreateLayout.vue`** component na reusable para sa lahat ng Create/Edit forms.

## Location
`resources/js/Components/CreateLayout.vue`

## Features
- ✅ Consistent header design (gradient banner with icon)
- ✅ Breadcrumb support
- ✅ Flexible max-width options
- ✅ Multiple icon choices
- ✅ Slot for form content

## Paano Gamitin

### Example 1: Simple Form (Customer Create)

```vue
<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    companies: { type: Array, default: () => [] }
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    company_id: null,
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <CreateLayout 
        title="Create Customer"
        subtitle="Add a new customer to the system"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'Customers', href: route('customers.index') },
            { label: 'Create' }
        ]"
        icon="plus"
        max-width="2xl"
    >
        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div>
                    <InputLabel for="first_name" value="First Name" />
                    <TextInput
                        id="first_name"
                        v-model="form.first_name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>

                <div>
                    <InputLabel for="last_name" value="Last Name" />
                    <TextInput
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('customers.index')" 
                        class="text-gray-600 hover:text-gray-900">
                        Cancel
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        Create Customer
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </CreateLayout>
</template>
```

### Example 2: Complex Form with Grid (Ticket Create)

```vue
<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    customers: Array,
    teams: Array,
    priorities: Array,
});

const form = useForm({
    customer_id: null,
    subject: '',
    description: '',
    priority: 'Low',
    team_id: null,
});

const submit = () => {
    form.post(route('alltickets.store'));
};
</script>

<template>
    <CreateLayout 
        title="Create New Ticket"
        subtitle="Submit a support ticket"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'All Tickets', href: route('alltickets.index') },
            { label: 'Create' }
        ]"
        icon="ticket"
        max-width="full"
    >
        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="customer" value="Customer" />
                    <select v-model="form.customer_id" class="mt-1 block w-full">
                        <option :value="null">Select Customer</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                            {{ customer.first_name }} {{ customer.last_name }}
                        </option>
                    </select>
                </div>

                <div>
                    <InputLabel for="priority" value="Priority" />
                    <select v-model="form.priority" class="mt-1 block w-full">
                        <option v-for="priority in priorities" :key="priority" :value="priority">
                            {{ priority }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="subject" value="Subject" />
                    <TextInput
                        id="subject"
                        v-model="form.subject"
                        class="mt-1 block w-full"
                        required
                    />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="description" value="Description" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        class="mt-1 block w-full"
                        rows="6"
                    ></textarea>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <Link :href="route('alltickets.index')" 
                    class="text-gray-600 hover:text-gray-900">
                    Cancel
                </Link>
                <PrimaryButton :disabled="form.processing">
                    Create Ticket
                </PrimaryButton>
            </div>
        </form>
    </CreateLayout>
</template>
```

## Props ng CreateLayout

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | **required** | Main heading sa header |
| `subtitle` | String | "Fill in the details below" | Subheading sa header |
| `breadcrumbItems` | Array | `[]` | Array of breadcrumb items |
| `icon` | String | "plus" | Icon type: 'plus', 'edit', 'view', 'ticket' |
| `maxWidth` | String | "2xl" | Container width: 'xl', '2xl', '4xl', '7xl', 'full' |

## Available Icons
- `plus` - Para sa Create forms
- `edit` - Para sa Edit forms
- `view` - Para sa Show/View pages
- `ticket` - Para sa Ticket-related forms

## Max Width Options
- `xl` - Small forms (login, simple creates)
- `2xl` - **Default** - Medium forms (most Create forms)
- `4xl` - Large forms
- `7xl` - Extra large forms
- `full` - Full width (complex forms with multiple columns)

## Mga Dapat I-update

Para gawing consistent lahat ng Create.vue files:

1. **Import ang CreateLayout**
   ```js
   import CreateLayout from '@/Components/CreateLayout.vue';
   ```

2. **Palitan ang template structure**
   - Remove: `<AuthenticatedLayout>`, `<Head>`, header banner code
   - Wrap: Form content sa `<CreateLayout>` with appropriate props

3. **I-adjust ang max-width** based sa form complexity
   - Simple forms: `max-width="2xl"`
   - Complex/grid forms: `max-width="full"`

4. **Add breadcrumbs** kung applicable

## Benefits

✅ **Consistent design** - All forms look the same
✅ **Easy to maintain** - Change once, apply everywhere
✅ **Reusable** - Use for Create, Edit, View pages
✅ **Flexible** - Props allow customization
✅ **Clean code** - Less duplication

---

**Next Steps:**
Gusto mo bang i-update ko ang mga existing Create.vue files to use this CreateLayout component?
