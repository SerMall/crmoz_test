<!-- Components/SalesOrderForm/OrderResultModal.vue -->
<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white rounded p-6 w-full max-w-2xl shadow-lg">
            <h3 class="text-xl font-semibold mb-4 text-green-600">✅ Sales Order Created</h3>

            <div class="space-y-1 text-sm">
                <p><strong>Order #:</strong> {{ order.order_number }}</p>
                <p><strong>Order Date:</strong> {{ order.order_date }}</p>
                <p><strong>Customer:</strong> {{ order.customer_name }}</p>
                <p><strong>Total:</strong> ${{ order.total }}</p>
            </div>

            <hr class="my-4">

            <h4 class="font-medium mb-2">📦 Items:</h4>
            <table class="w-full text-sm border">
                <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-2 py-1 border">Item</th>
                    <th class="px-2 py-1 border">Qty</th>
                    <th class="px-2 py-1 border">Rate</th>
                    <th class="px-2 py-1 border">Total</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, index) in order.line_items" :key="index" class="border-t">
                    <td class="px-2 py-1">{{ item.name }}</td>
                    <td class="px-2 py-1">{{ item.quantity }}</td>
                    <td class="px-2 py-1">${{ item.rate }}</td>
                    <td class="px-2 py-1">${{ (item.quantity * item.rate).toFixed(2) }}</td>
                </tr>
                </tbody>
            </table>

            <div class="space-y-1 text-sm">
                <p><strong>Notes:</strong></p>
                <p class="whitespace-pre-wrap">{{ order.notes }}</p>
            </div>

            <div class="mt-6 text-right">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        @click="$emit('close')">
                    OK
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps(['order'])
defineEmits(['close'])
</script>
