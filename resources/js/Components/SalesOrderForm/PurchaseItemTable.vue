<template>
    <div>
        <h3 class="text-lg font-semibold mb-2">Purchase Items</h3>
        <table class="w-full border border-gray-300 text-sm">
            <thead class="bg-gray-100">
            <tr>
                <th class="border px-2 py-1">Item</th>
                <th class="border px-2 py-1">Account</th>
                <th class="border px-2 py-1">Quantity</th>
                <th class="border px-2 py-1">Rate</th>
                <th class="border px-2 py-1">Amount</th>
                <th class="border px-2 py-1"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item, index) in items" :key="index">
                <div class="flex gap-2 items-start">
                    <img
                        :src="item.image_url || '/placeholder.png'"
                        class="w-10 h-10 object-cover rounded"
                    />
                    <div class="text-sm w-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium">{{ item.name }}</div>
                                <div class="text-gray-500 text-xs">SKU: {{ item.sku }}</div>
                            </div>
                        </div>

                        <textarea
                            v-model="item.description"
                            rows="2"
                            class="input mt-1 text-xs w-full"
                            placeholder="Product description"
                        />
                    </div>
                </div>
                <td class="border p-2">
                    <select v-model="item.account" class="input w-full">
                        <option disabled value="">Select account</option>
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.name">{{ acc.name }}</option>
                    </select>
                </td>
                <td class="border p-2 text-center">
                    <div class="flex items-center gap-1">
                        <input
                            v-model.number="item.quantity"
                            type="number"
                            class="input w-20"
                            @input="updateAmount(index)"
                        />
                    </div>
                </td>
                <td class="border p-2 text-right">
                    <input v-model.number="item.purchase_rate"
                           type="number"
                           class="input"
                           @input="updateAmount(index)"
                    />
                </td>
                <td class="border p-2 text-right">
                    {{ (item.quantity * item.purchase_rate).toFixed(2) }}
                </td>
                <td class="w-8 text-red-600 text-center cursor-pointer" @click="removeItem(index)">
                    <span class="text-red-600">✖</span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { computed, reactive, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
})
const emit = defineEmits(['update:modelValue', 'empty']);

const items = reactive(props.modelValue)
const accounts = reactive([])

const fetchAccounts = async () => {
    try {
        const response = await axios.get('/api/zoho/inventory/accounts')
        accounts.splice(0, accounts.length, ...response.data.data)
    } catch (e) {
        console.error('Failed to fetch accounts:', e)
    }
}

const onProductChange = (item) => {
    const selected = products.find(prod => prod.item_id === item.item_id)
    if (!selected) return

    item.name = selected.name
    item.sku = selected.sku
    item.description = selected.description || ''
    item.purchase_rate = selected.purchase_rate || 0
    item.stock_on_hand = selected.stock_on_hand || 0
    item.image_url = selected.image_url || '/placeholder.png'
}

const resetItem = (item) => {
    item.name = ''
    item.sku = ''
    item.description = ''
    item.image_url = ''
    item.item_id = ''
    item.purchase_rate = 0
    item.stock_on_hand = 0
}

const getPurchaseQuantity = (item) => {
    const diff = (item.stock_on_hand ?? 0) - (item.quantity ?? 0)
    return diff < 0 ? Math.abs(diff) : 0
}

onMounted(() => {
    items.forEach(item => {
        item.quantity = getPurchaseQuantity(item);
    });
});

watch(items, () => {
    emit('update:modelValue', items);
}, { deep: true });

const updateAmount = (index) => {
    const item = items[index];
    item.amount = (item.quantity * item.rate).toFixed(2);
};

const removeItem = (index) => {
    items.splice(index, 1);

    if (items.length === 0) {
        emit('empty');
    }
};

// onMounted(fetchAccounts)
</script>

<style scoped>
.input {
    @apply border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400;
}
</style>
