<template>
    <div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-40 flex items-start justify-center z-50">
        <div class="bg-white rounded-md shadow-md w-full max-w-xl mt-16 p-6">
            <h2 class="text-xl font-semibold mb-6">Create New Customer</h2>

            <form @submit.prevent="submitForm" class="space-y-4">
                <!-- Customer Type -->
                <div class="flex items-center gap-4">
                    <label class="w-40 text-sm font-medium text-gray-700">Customer Type</label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio"
                                   value="business"
                                   v-model="form.customer_sub_type"
                            />
                            Business
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio"
                                   value="individual"
                                   v-model="form.customer_sub_type"
                            />
                            Individual
                        </label>
                    </div>
                </div>
                <!-- Primary Contact (First + Last Name) -->
                <div class="flex items-center gap-4">
                    <label class="w-40 text-sm font-medium text-gray-700">Primary Contact</label>
                    <div class="flex gap-2 w-full">
                        <input v-model="form.contact_persons[0].first_name"
                               type="text"
                               placeholder="First Name"
                               class="input w-1/2"
                        />
                        <input v-model="form.contact_persons[0].last_name"
                               type="text"
                               placeholder="Last Name"
                               class="input w-1/2"
                        />
                    </div>
                </div>
                <!-- Company Name -->
                <div class="flex items-center gap-4">
                    <label class="w-40 text-sm font-medium text-gray-700">Company Name</label>
                    <input v-model="form.company_name"
                           type="text"
                           class="input w-full"
                    />
                </div>
                <!-- Display Name -->
                <div class="flex items-center gap-4">
                    <label class="w-40 text-sm font-medium text-gray-700">Display Name</label>
                    <div class="relative w-full">
                        <input v-model="form.contact_name"
                               type="text"
                               class="input w-full"
                        />
                        <p v-if="errors.contact_name" class="text-red-500 text-xs mt-1">{{ errors.contact_name }}</p>
                    </div>
                </div>
                <!-- Email -->
                <div class="flex items-center gap-4">
                    <label class="w-40 text-sm font-medium text-gray-700">Email</label>
                    <input v-model="form.contact_persons[0].email"
                           type="email"
                           class="input w-full"
                    />
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded"
                    >

                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded"
                    >
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean
});

const emit = defineEmits(['update:modelValue', 'customer-created']);

const errors = reactive({});

const form = reactive({
    contact_name: '',
    customer_sub_type: 'business',
    contact_type: 'customer',
    company_name: '',
    contact_persons: [
        {
            first_name: '',
            last_name: '',
            email: ''
        }
    ]
});

// Set Contact_name = first_name + last_name
watch(() => form.contact_persons[0].first_name, (val) => {
    form.contact_name = val + (form.contact_persons[0].last_name ? ' ' + form.contact_persons[0].last_name : '');
});
watch(() => form.contact_persons[0].last_name, (val) => {
    form.contact_name = (form.contact_persons[0].first_name || '') + (val ? ' ' + val : '');
});

const closeModal = () => {
    resetForm();
    emit('update:modelValue', false);
};

const resetForm = () => {
    form.contact_name = '';
    form.contact_type = 'business';
    form.company_name = '';
    form.contact_persons[0].first_name = '';
    form.contact_persons[0].last_name = '';
    form.contact_persons[0].email = '';
};

const submitForm = async () => {
    errors.contact_name = form.contact_name ? '' : 'Display Name is required';
    // errors.email = form.contact_persons[0].email ? '' : 'Email is required';
    // errors.company_name = form.company_name ? '' : 'Company is required';

    if (Object.values(errors).some(Boolean)) return;

    try {

        console.log(JSON.stringify(JSON.parse(JSON.stringify(form))))
        const response = await fetch('/api/zoho/inventory/customers/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(JSON.parse(JSON.stringify(form)))
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Failed');
        }

        alert('Customer created successfully!');
        emit('customer-created', result.data.contact);
        closeModal();
    } catch (error) {
        console.error('Failed to create customer:', error);
    }
};

</script>

<style scoped>
.input {
    @apply border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-500;
}
</style>
