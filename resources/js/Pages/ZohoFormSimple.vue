<template>
    <form @submit.prevent="submitForm">
        <h2>ZOHO CRM Deals</h2>
        <div>
            <label>Deal Name:</label>
            <input v-model.trim="form.deal_name" required />
        </div>
        <div>
            <label>Deal Stage:</label>
            <select id="deal_stage"
                    v-model="form.deal_stage"
                    required
            >
                <option disabled value="">Select a stage</option>
                <option v-for="(label, value) in stages" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>
        </div>
        <div>
            <label>Deal Closing Date:</label>
            <input v-model="form.deal_closing_date"
                   required
                   type="date"
            />
        </div>

        <h2>ZOHO CRM Account Form</h2>
        <div>
            <label>Account Name:</label>
            <input v-model.trim="form.account_name" required />
        </div>
        <div>
            <label>Account Website:</label>
            <input v-model.trim="form.account_website"
                   placeholder="https://website.com"
                   type="url"
            />
        </div>
        <div>
            <label>Account Phone:</label>
            <input v-model.trim="form.account_phone"
                   placeholder="XXXYY1234567"
                   maxlength="12"
            />
        </div>

        <div v-if="error" style="color:red">{{ error }}</div>
        <div v-if="success" style="color:green">{{ success }}</div>

        <button type="submit" :disabled="loading">
            {{ loading ? 'Submitting...' : 'Submit' }}
        </button>
    </form>
</template>

<script setup>

import { ref, onMounted} from 'vue';
import axios from 'axios';

const form = ref({
    deal_name: '',
    deal_stage: '',
    deal_closing_date: '',
    account_name: '',
    account_website: '',
    account_phone: '',
});

const loading = ref(false);
const error = ref('');
const success = ref('');
const stages = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('/api/zoho/stageslist')
        stages.value = response.data.data;
        // stages.value = Object.entries(response.data.data).map(([value, label]) => ({
        //     value,
        //     label
        // }));
    } catch (err) {
        console.error('Error loading deal stages', err)
    }
})

const validate = () => {
    error.value = '';
    if (!form.value.deal_name || !form.value.deal_stage || !form.value.deal_closing_date ||
        !form.value.account_name) {
        error.value = 'All fields are required.';

        return false;
    }
    return true;
};

const submitForm = async () => {
    if (!validate()) return;

    loading.value = true;
    error.value = '';
    success.value = '';

    try {
        const response = await fetch('/api/zoho/create', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(form.value)
        });

        const result = await response.json();

        if (!response.ok) throw new Error(result.message || 'Failed');
        success.value = result.message || 'Success!';
        form.value = {
            deal_name: '',
            deal_stage: '',
            deal_closing_date: '',
            account_name: '',
            account_website: '',
            account_phone: ''
        };
    } catch (e) {
        error.value = e.message;
    }
    loading.value = false;
};
</script>
