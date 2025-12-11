# ShowLayout Component Usage Guide

## Component Created
`resources/js/Components/ShowLayout.vue` - Reusable layout for all Show/Detail pages

## Features
- ✅ Gradient hero header with icon
- ✅ Action buttons slot (Edit, Delete)
- ✅ Consistent spacing and styling
- ✅ Multiple icon options

## Available Icons
- `document` - Generic documents/records
- `user` - Single user/customer
- `users` - Multiple users/teams
- `building` - Companies/departments
- `tag` - Tags/labels
- `shield` - Roles/permissions
- `ticket` - Support tickets
- `briefcase` - Business/work items

## Usage Pattern

```vue
<template>
    <ShowLayout
        :title="resource.name"
        subtitle="Resource description"
        icon="user"
        :actions="hasEditOrDeletePermission"
    >
        <template #actions>
            <Link v-if="canEdit" :href="editRoute" class="...">Edit</Link>
            <DangerButton v-if="canDelete" @click="confirmDeletion">Delete</DangerButton>
        </template>

        <!-- Main content -->
        <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-400">
            <div class="p-6">
                <!-- Details here -->
            </div>
        </div>
    </ShowLayout>
</template>

<script setup>
import ShowLayout from '@/Components/ShowLayout.vue';
// ... other imports
</script>
```

## Files to Update (11 remaining)
1. ✅ Customer/Show.vue - DONE
2. Employee/Show.vue
3. Department/Show.vue
4. Company/Show.vue
5. HelpdeskTeams/Show.vue
6. Users/Show.vue
7. Roles/Show.vue
8. Tags/Show.vue
9. TeamTickets/Show.vue
10. MyTickets/Show.vue
11. Customer_Dashboard/Show.vue (special case)

## Standard Replacements

### 1. Remove Old Header
```vue
<!-- OLD -->
<template #header>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ title }}
    </h2>
</template>
```

### 2. Replace with ShowLayout
```vue
<!-- NEW -->
<ShowLayout
    :title="resource.name"
    subtitle="Description"
    icon="user"
    :actions="hasPermissions"
>
```

### 3. Update Action Buttons
```vue
<!-- OLD -->
<Link :href="route('edit')" class="inline-flex items-center px-4 py-2 bg-gray-800 ...">
    Edit
</Link>

<!-- NEW -->
<Link :href="route('edit')" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-4M11 4l10 10M11 4v6h6"/>
    </svg>
    Edit
</Link>
```

### 4. Add Section Headers with Icons
```vue
<div class="flex items-center mb-4">
    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-2 rounded-lg mr-2">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path .../>
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-gray-900">Section Title</h3>
</div>
```

### 5. Update Badge Styles
```vue
<!-- OLD -->
<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
    Status
</span>

<!-- NEW -->
<span class="px-3 py-1.5 inline-flex items-center text-xs leading-5 font-bold rounded-xl shadow-sm bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200">
    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Status
</span>
```

## Benefits
- ✅ Consistent design across all Show pages
- ✅ Easy maintenance - update once, applies everywhere
- ✅ Professional gradient styling
- ✅ Better UX with icons and visual hierarchy
