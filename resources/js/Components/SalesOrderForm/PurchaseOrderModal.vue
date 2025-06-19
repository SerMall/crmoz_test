<template>
    <div v-if="visible" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[800px] p-5 shadow-lg relative">
            <h2 class="text-xl font-bold mb-4">Confirm Purchase Order</h2>

            <div class="flex flex-col gap-3 mb-4">
                <!-- Vendor Name -->
                <div class="flex items-start gap-2">
                    <label class="w-40 text-sm font-medium text-red-500 mt-2">Vendor Name *</label>
                    <div class="flex flex-col w-full">
                        <select v-model="form.vendor_id" class="input w-full">
                            <option value="">Select a Vendor</option>
                            <option v-for="vendor in vendors"
                                    :key="vendor.contact_id"
                                    :value="vendor.contact_id">
                                {{ vendor.contact_name }}
                            </option>
                        </select>
                        <p v-if="errors.vendor_id" class="text-red-500 text-xs mt-1">{{ errors.vendor_id }}</p>
                    </div>
                </div>
                <!-- Delivery Address -->
                <div class="flex items-start gap-2">
                    <label class="w-40 text-sm font-medium text-red-500 mt-2">Delivery Address *</label>
                    <div class="flex flex-col w-full gap-2">
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="radio" value="org" v-model="deliveryAddress" />
                                Organization
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" value="customer" v-model="deliveryAddress" />
                                Customer
                            </label>
                        </div>
                        <!-- If customer selected -->
                        <div v-if="deliveryAddress === 'customer'">
                            <select v-model="form.delivery_customer_id" class="input w-full">
                                <option disabled value="">Select a Customer</option>
                                <option v-for="customer in customers"
                                        :key="customer.contact_id"
                                        :value="customer.contact_id">
                                    {{ customer.contact_name }}
                                </option>
                            </select>
                        </div>
                        <!-- If organization selected -->
                        <div v-else class="text-sm text-gray-600">
                            {{ organizationAddress }}
                        </div>

                        <p v-if="errors.deliveryAddress" class="text-red-500 text-xs">{{ errors.deliveryAddress }}</p>
                    </div>
                </div>
                <!-- PO Number -->
                <div class="flex items-start gap-2">
                    <label class="w-40 text-sm font-medium text-red-500 mt-2">Purchase Order # *</label>
                    <div class="flex flex-col w-full">
                        <input v-model="form.purchaseorder_number"
                               class="input w-full"
                               type="text"
                        />
                        <p v-if="errors.purchaseorder_number" class="text-red-500 text-xs mt-1">{{ errors.purchaseorder_number }}</p>
                    </div>
                </div>
                <!-- Reference # -->
                <div class="flex items-start gap-2">
                    <label class="w-40 text-sm font-medium">Reference #</label>
                    <input v-model="form.reference_number"
                           class="input w-full"
                           type="text"
                    />
                </div>
                <!-- Date -->
                <div class="flex items-start gap-2">
                    <label class="w-40 text-sm font-medium">Date</label>
                    <input v-model="form.date"
                           class="input w-full"
                           type="date"
                    />
                </div>
            </div>

            <PurchaseItemTable v-model="form.items"
                               @empty="$emit('cancel')"
            />

            <div class="flex justify-end mt-4 gap-2">
                <button @click="$emit('cancel')" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
                <button @click="submitPurchaseOrder" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Confirm PO
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';
import PurchaseItemTable from '@/Components/SalesOrderForm/PurchaseItemTable.vue';
import log from "tailwindcss/src/util/log.js";

const props = defineProps({
    visible: Boolean,
    items: {
        type: Array,
        required: true
    }
});
const emit = defineEmits(['cancel', 'confirm']);

const form = reactive({
    contact_id: '',
    vendor_name: '',
    vendor_id: '',
    purchaseorder_number: '',
    reference_number: '',
    date: dayjs().format('YYYY-MM-DD'),
    delivery_customer_id: '',
    delivery_org_address_id: '',
    items: [],
});

const errors = reactive({});
const vendors = reactive([]);
const customers = reactive([]);
const deliveryAddress = ref('org');
const organizationAddress = ref('Illinois, U.S.A');

onMounted(async () => {
    try {
        const response = await axios.get('/api/zoho/inventory/vendorslist');
        vendors.splice(0, vendors.length, ...response.data.data);
    } catch (e) {
        console.error('Failed to fetch vendors:', e);
    }

    try {
        const response = await axios.get('/api/zoho/inventory/customerslist');
        customers.splice(0, customers.length, ...response.data.data);
    } catch (err) {
        console.error('Error loading customers list', err);
    }

    try {
        const response = await axios.get('/api/zoho/inventory/nextpurchasenumber');
        form.purchaseorder_number = response.data.data;
    } catch (err) {
        console.error('Error loading Next Purchase Order Number', err);
    }

    try {
        const response = await axios.get('/api/zoho/inventory/organizationaddress');
        const address = response.data.data;

        organizationAddress.value = [address.country, address.state, address.zip, address.city]
            .filter(Boolean)
            .join(', ');
    } catch (err) {
        console.error('Error loading Organization Address', err);
    }
});

const filteredItems = computed(() =>
    props.items
        .filter(item => item.include_in_po && (item.stock_on_hand - item.quantity) < 0)
        .map(item => ({
            item_id: item.item_id,
            name: item.name,
            description: item.description,
            quantity: Math.abs(item.stock_on_hand - item.quantity),
            purchase_rate: item.purchase_rate,
        }))
);

watch(filteredItems, val => {
    form.items = val;
}, { immediate: true });

const submitPurchaseOrder = async () => {
    errors.vendor_id = form.vendor_id ? '' : 'Vendor is required';
    errors.deliveryAddress =
        deliveryAddress.value === 'customer' && !form.delivery_customer_id
            ? 'Please select a customer'
            : '';
    errors.purchaseorder_number = form.purchaseorder_number ? '' : 'PO Number required';

    if (Object.values(errors).some(Boolean)) return;

    const payload = {
        vendor_id: form.vendor_id,
        delivery_customer_id: form.delivery_customer_id,
        delivery_org_address_id: form.delivery_org_address_id,
        purchaseorder_number: form.purchaseorder_number,
        reference_number: form.reference_number,
        date: form.date,
        items: form.items.map(item => ({
            item_id: item.item_id,
            name: item.name,
            description: item.description,
            quantity: item.quantity,
            purchase_rate: item.purchase_rate,
        })),
    };

    try {
        const response = await axios.post('/api/zoho/inventory/purchaseorders/create', payload);

        alert('Purchase Order created successfully!');
        emit('confirm');
    } catch (e) {
        error.value = e.message;
    }
};
</script>

<style scoped>
.input {
    @apply w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400;
}
</style>
