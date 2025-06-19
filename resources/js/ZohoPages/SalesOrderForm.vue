<template>
    <div class="bg-white rounded shadow-md p-6 max-w-5xl mx-auto space-y-6">
        <h2 class="text-2xl font-semibold">🛒 New Sales Order</h2>

        <form @submit.prevent="submitForm">
            <!-- Customer select -->
            <div class="mt-4 flex items-center gap-4">
                <label for="customer" class="w-40 text-red-600 font-medium text-sm">
                    Customer Name<span class="text-red-600">*</span>
                </label>
                <div class="relative w-[40%]">
                    <select v-model="form.contact_id"
                            class="border border-gray-300 bg-gray-100 rounded px-2 py-1 w-full"
                    >
                        <option value="">Select or add a customer</option>
                        <option v-for="customer in customers"
                                :key="customer.contact_id"
                                :value="customer.contact_id">
                            {{ customer.customer_name }}
                        </option>

                        <option disabled>──────────────</option>
                        <option value="__new__">➕ New Customer</option>
                    </select>
                    <p v-if="errors.contact_id" class="text-red-500 text-xs mt-1">{{ errors.contact_id }}</p>
                    <button
                        type="button"
                        class="absolute top-1/2 -right-10 -translate-y-1/2 bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded shadow text-sm"
                        @click="showCustomerModal = true"
                    >
                        🔍
                    </button>

                    <CustomerSearchModal
                        v-if="showCustomerModal"
                        :customers="customers"
                        @close="showCustomerModal = false"
                        @select="handleCustomerSelect"
                    />

                    <CreateCustomerModal
                        v-model="createCustomerModal"
                        @customer-created="handleCustomerCreated"
                    />
                </div>
            </div>
            <!-- Sales Order Number -->
            <div class="mt-4 flex items-center gap-4">
                <label for="order_number" class="w-40 text-red-600 font-medium text-sm">
                    Sales Order#<span class="text-red-600">*</span>
                </label>
                <div class="relative w-[40%]">
                    <input v-model="form.order_number"
                           class="border border-gray-300 rounded px-2 py-1 w-full"
                           type="text"
                           maxlength="20"
                    />
                    <p v-if="errors.order_number" class="text-red-500 text-xs mt-1">{{ errors.order_number }}</p>
                </div>
            </div>
            <!-- Reference -->
            <div class="mt-4 flex items-center gap-4">
                <label for="reference" class="w-40 text-gray-700 font-medium text-sm">
                    Reference#
                </label>
                <div class="relative w-[40%]">
                    <input v-model="form.reference"
                           class="border border-gray-300 rounded px-2 py-1 w-full"
                           type="text"
                    />
                </div>
            </div>
            <!-- Sales Order Date -->
            <div class="mt-4 flex items-center gap-4">
                <label for="order_date" class="w-40 text-red-600 font-medium text-sm">
                    Sales Order Date<span class="text-red-600">*</span>
                </label>
                <div class="relative w-[40%]">
                    <input v-model="form.order_date"
                           class="border border-gray-300 rounded px-2 py-1 w-full"
                           type="date"
                    />
                    <p v-if="errors.order_date" class="text-red-500 text-xs mt-1">{{ errors.order_date }}</p>
                </div>
            </div>
            <!-- ItemTable -->
            <div class="mt-4 flex items-center gap-4">
                <div class="relative w-[60%]">
                    <ItemTable
                        v-model="form.items"
                        @update:subtotal="val => subtotal = val"
                    />
                    <p v-if="errors.items" class="text-red-500 text-xs mt-1">{{ errors.items }}</p>
                </div>
            </div>
            <!-- Customer Notes -->
            <div class="mt-4 flex items-center gap-4 w-[60%]">
                <div>
                    <label class="block font-medium mb-1">Customer Notes</label>
                    <textarea v-model="form.notes"
                              class="border border-gray-300 rounded px-2 py-1"
                              rows="3"
                    ></textarea>
                </div>
                <OrderSummary :subtotal="subtotal" @update:total="val => total = val" />
            </div>
            <div class="mt-4 flex items-center gap-4">
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                        type="submit"
                >
                    Send
                </button>
            </div>

            <PurchaseOrderModal
                :visible="showPurchaseOrderModal"
                :items="form.items"
                @cancel="showPurchaseOrderModal = false"
                @confirm="handlePurchaseOrderConfirmed"
            />
        </form>

        <OrderResultModal
            v-if="showResultModal"
            :order="createdOrder"
            @close="handleOrderResultClose"
        />
    </div>
</template>

<script setup>
import {reactive, ref, onMounted, computed, warn, watch} from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';
import CustomerSearchModal from '../Components/SalesOrderForm/CustomerSearchModal.vue';
import CreateCustomerModal from '../Components/SalesOrderForm/CreateCustomerModal.vue';
import PurchaseOrderModal from '../Components/SalesOrderForm/PurchaseOrderModal.vue';
import ItemTable from '@/Components/SalesOrderForm/ItemTable.vue';
import OrderResultModal from '@/Components/SalesOrderForm/OrderResultModal.vue';
import OrderSummary from '@/Components/SalesOrderForm/OrderSummary.vue';
import log from "tailwindcss/src/util/log.js";

const form = reactive({
    contact_id: '',
    order_number: '',
    reference: '',
    order_date: dayjs().format('YYYY-MM-DD'),
    shipment_date: '',
    payment_terms: 'Due on Receipt',
    notes: '',
    items: [{
        name: '',
        item_id: '',
        quantity: 1,
        rate: 0,
        purchase_rate: 0,
        include_in_po: false
    }]
});

const customers = reactive([]);
const showCustomerModal = ref(false);
const createCustomerModal = ref(false);
const showPurchaseOrderModal = ref(false);
const pendingSubmit = ref(false);
const submitTriggered = ref(false);
const showResultModal = ref(false);
const createdOrder = ref(null);
const total = ref(0);
const errors = reactive({});

onMounted(async () => {
    try {
        const response = await axios.get('/api/zoho/inventory/customerslist');
        customers.splice(0, customers.length, ...response.data.data);
    } catch (err) {
        console.error('Error loading customers list', err);
    }

    try {
        const response = await axios.get('/api/zoho/inventory/nextordernumber');
        form.order_number = response.data.data;
    } catch (err) {
        console.error('Error loading Next Order Number', err);
    }
});

const handleCustomerSelect = async (customer) => {
    form.contact_id = customer.contact_id;
    showCustomerModal.value = false;
};
// open modal for create new castomer
watch(() => form.contact_id, (val) => {
    if (val === '__new__') {
        createCustomerModal.value = true;
        form.contact_id = ''; // очищаємо вибір
    }
});
// add new customer
const handleCustomerCreated = (newCustomer) => {
    const added = {
        contact_id: newCustomer.contact_id,
        customer_name: newCustomer.customer_name || newCustomer.display_name || newCustomer.contact_name,
        ...newCustomer
    };
    customers.push(added);
    form.contact_id = added.contact_id;
};

const subtotal = computed(() =>
    form.items.reduce((sum, item) => sum + item.quantity * item.rate, 0)
)

const submitForm = async () => {

    errors.contact_id = form.contact_id ? '' : 'Customer is required';
    errors.order_number = form.order_number ? '' : 'Order Number is required';
    errors.order_date = form.order_date ? '' : 'Sales Order Date is required';

    const formArray = JSON.parse(JSON.stringify(form));
    const hasValidItem = formArray.items.some(item => !!item.item_id);
    errors.items = hasValidItem ? '' : 'Please add product';

    if (Object.values(errors).some(Boolean)) return;

    submitTriggered.value = true;
    const hasPOItems = form.items.some(i => i.include_in_po && getAdjustedStock(i) < 0);
    if (hasPOItems && !pendingSubmit.value && submitTriggered.value) {
        showPurchaseOrderModal.value = true;
        return;
    }

    try {
        const response = await fetch('/api/zoho/inventory/salesorders/create', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(JSON.parse(JSON.stringify(form)))
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed');
        }

        if (result.data.code == 0) {
            const orderData = result.data.salesorder;

            createdOrder.value = {
                order_id: orderData.salesorder_id,
                order_number: orderData.salesorder_number,
                order_date: orderData.date,
                customer_name: orderData.customer_name,
                total: orderData.total,
                notes: orderData.notes,
                line_items: orderData.line_items || []
            };

            showResultModal.value = true;
            submitTriggered.value = false;
        }
    } catch (e) {
        error.value = e.message;
    }
};

const getAdjustedStock = (item) => {
    return (item.stock_on_hand || 0) - (item.quantity || 0)
}

const handlePurchaseOrderConfirmed = (result) => {
    showPurchaseOrderModal.value = false;
    pendingSubmit.value = true;

    submitForm();
};

const handleOrderResultClose = () => {
    showResultModal.value = false;
    // clear form
    form.contact_id = '';
    form.order_number = '';
    form.reference = '';
    form.order_date = dayjs().format('YYYY-MM-DD');
    form.shipment_date = '';
    form.payment_terms = 'Due on Receipt';
    form.notes = '';
    form.items.splice(0, form.items.length, {
        name: '',
        item_id: '',
        quantity: 1,
        rate: 0,
        purchase_rate: 0,
        include_in_po: false
    });

    axios.get('/api/zoho/inventory/nextordernumber')
        .then(response => form.order_number = response.data.data)
        .catch(() => console.warn('Could not load new order number'));

    pendingSubmit.value = false;
};
</script>
