<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded shadow-md w-full max-w-xl">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">Advanced Customer Search</h2>
                <button @click="$emit('close')" class="text-red-600 text-lg font-bold">×</button>
            </div>

            <input v-model="searchQuery" type="text" placeholder="Search by name..." class="w-full border rounded px-2 py-1 mb-4" />

            <table class="w-full text-left border">
                <thead>
                <tr>
                    <th class="border px-2 py-1">Customer</th>
                    <th class="border px-2 py-1">Email</th>
                    <th class="border px-2 py-1">Company</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="customer in filteredCustomers" :key="customer.contact_id" @click="$emit('select', customer)" class="hover:bg-gray-100 cursor-pointer">
                    <td class="border px-2 py-1">{{ customer.customer_name }}</td>
                    <td class="border px-2 py-1">{{ customer.email }}</td>
                    <td class="border px-2 py-1">{{ customer.company_name }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps(['customers'])
const emit = defineEmits(['close', 'select'])

const searchQuery = ref('')
const filteredCustomers = computed(() =>
    props.customers.filter(contact =>
        contact.customer_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
)
</script>
