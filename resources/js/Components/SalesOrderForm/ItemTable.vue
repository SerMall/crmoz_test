<template>
    <div>
        <h3 class="text-lg font-semibold mb-2">Item Table</h3>
        <table class="w-full border border-gray-300 text-sm">
            <thead class="bg-gray-100">
            <tr>
                <th class="border px-2 py-1">Item Details</th>
                <th class="border px-2 py-1">Quantity</th>
                <th class="border px-2 py-1">Rate</th>
                <th class="border px-2 py-1">Amount</th>
                <th class="border px-2 py-1"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item, index) in items" :key="index">
                <td class="border p-2 w-1/3 align-top">
                    <!-- Select: show if Item nonSelected -->
                    <div v-if="!item.name">
                        <select
                            v-model="item.item_id"
                            class="input w-full"
                            @change="onProductChange(item)"
                        >
                            <option disabled value="">Select item</option>
                            <option
                                v-for="product in products"
                                :key="product.item_id"
                                :value="product.item_id"
                            >
                                {{ product.name }}
                            </option>
                        </select>
                    </div>

                    <div v-else class="flex gap-2 items-start">
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
                                <!-- Delete Item -->
                                <button
                                    type="button"
                                    class="text-xs text-blue-600 underline"
                                    @click="resetItem(item)"
                                >
                                    ✖
                                </button>
                            </div>

                            <textarea
                                v-model="item.description"
                                rows="2"
                                class="input mt-1 text-xs w-full"
                                placeholder="Product description"
                            />
                        </div>
                    </div>
                </td>
                <td class="border p-1 align-top">
                    <div class="flex items-center gap-1">
                        <input
                            v-model.number="item.quantity"
                            type="number"
                            class="input w-20"
                            min="1"
                            step="1"
                            @input="updateAmount(index)"
                        />
                    </div>
                    <!-- New Stock on Hand -->
                    <div
                        v-if="item.name"
                        class="text-xs mt-1"
                        :class="{
                            'text-red-600': getAdjustedStock(item) < 0,
                            'text-gray-500': getAdjustedStock(item) >= 0
                        }"
                    >
                        Stock on Hand: {{ getAdjustedStock(item) }}
                    </div>
                    <!-- If stock less than 0 show checkbox -->
                    <div v-if="getAdjustedStock(item) < 0" class="text-xs text-red-600 mt-1">
                        <label class="flex items-center gap-1">
                            <input type="checkbox" v-model="item.include_in_po" />
                            Add to Purchase Order: {{ Math.abs(getAdjustedStock(item)) }}
                        </label>
                    </div>
                </td>
                <td class="border p-1">
                    <input v-model.number="item.rate"
                           type="number"
                           class="input"
                           @input="updateAmount(index)"
                    />
                </td>
                <td class="border p-1 text-right">
                    {{ (item.quantity * item.rate).toFixed(2) }}
                </td>

                <td class="w-8 text-red-600 text-center cursor-pointer" @click="removeItem(index)">
                    ✖
                </td>
            </tr>
            </tbody>
        </table>
        <button @click="addItem" class="mt-3 bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
            ➕ Add Item
        </button>
    </div>
</template>

<script setup>
import { reactive, watch, onMounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:modelValue', 'update:subtotal'])
const items = reactive(props.modelValue)
// const items = ref([...props.modelValue]);
const products = reactive([])

const fetchProducts = async () => {
    try {
        const response = await axios.get('/api/zoho/inventory/productslist')
        products.splice(0, products.length, ...response.data.data)

    } catch (e) {
        console.error('Failed to fetch products:', e)
    }
}

const onProductChange = (item) => {
    const selected = products.find(prod => prod.item_id === item.item_id)
    if (!selected) return

    item.name = selected.name
    item.sku = selected.sku
    item.description = selected.description || ''
    item.rate = selected.rate || 0
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
    item.rate = 0
    item.purchase_rate = 0
    item.stock_on_hand = 0
}

function setRateFromProduct(item) {
    const selected = products.find(p => p.item_id === item.item_id)
    if (selected) {
        item.rate = selected.rate
    }
}

const addItem = () => {
    items.push({ name: '', quantity: 1, rate: 0, purchase_rate: 0, include_in_po: false })
}

const removeItem = (index) => {
    items.splice(index, 1)
}

const updateAmount = (index) => {
    const item = items[index];
    // Sum
    item.amount = (item.quantity * item.rate).toFixed(2);
    // If stock_on_hand undefined (new product) — default
    if (typeof item.stock_on_hand !== 'number') {
        item.stock_on_hand = 0;
    }
};

const getAdjustedStock = (item) => {
    return item.stock_on_hand - item.quantity;
};

const subtotal = computed(() =>
    items.reduce((sum, item) => sum + item.quantity * item.rate, 0)
);
// Watch subtotal and emit up
watch(subtotal, () => {
    emit('update:subtotal', subtotal.value);
});
// Synchronize with parent component
watch(items, () => {
    emit('update:modelValue', items);
}, { deep: true });

onMounted(fetchProducts);
</script>

<style scoped>
.input {
    @apply w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400;
}
</style>
