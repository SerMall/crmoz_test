<template>
    <Head title="ZOHO Form " />

    <form @submit.prevent="submitForm" style="margin-left: 1.5em">

        <h2 class="text-xl font-semibold leading-tight text-gray-800 mt-4">
            ZOHO CRM Deals
        </h2>

        <div class="mt-4">
            <InputLabel for="deal_name" value="Deal Name:" />

            <TextInput
                id="deal_name"
                type="text"
                class="mt-1 block w-3/4 form-control"
                style="width: 30%"
                v-model.trim="form.deal_name"
                required
                autofocus
                maxlength="20"
            />
        </div>

        <div class="mt-4">
            <InputLabel for="deal_stage" value="Deal Stage:" />

            <VueSelect
                id="deal_stage"
                class="mt-1 block w-3/4"
                style="width: 30%"
                v-model="form.deal_stage"
                required
                :options="stages"
                label="label"
                :reduce="stage => stage.value"
                placeholder="Select an option"
            />
        </div>

        <div class="mt-4">
            <InputLabel for="deal_closing_date" value="Deal Closing Date:" />

            <DateInput
                id="deal_closing_date"
                type="date"
                class="mt-1 block w-3/4"
                style="width: 30%"
                v-model.trim="form.deal_closing_date"
                placeholder="DD.MM.YYYY"
                required
            />
        </div>

        <h2
            class="text-xl font-semibold leading-tight text-gray-800 mt-4"
        >
            ZOHO CRM Account Form
        </h2>

        <div class="mt-4">
            <InputLabel for="account_name" value="Account Name:" />

            <TextInput
                id="account_name"
                type="text"
                class="mt-1 block w-3/4"
                style="width: 30%"
                v-model.trim="form.account_name"
                required
                maxlength="20"
            />
        </div>

        <div class="mt-4">
            <InputLabel for="account_website" value="Account Website:" />

            <TextInput
                id="account_website"
                type="url"
                class="mt-1 block w-3/4"
                style="width: 30%"
                v-model.trim="form.account_website"
                placeholder="https://website.com"
            />
        </div>

        <div class="mt-4">
            <InputLabel for="account_phone" value="Account Phone:" />

            <TextInput
                id="account_phone"
                type="text"
                class="mt-1 block w-3/4"
                style="width: 30%"
                v-model.trim="form.account_phone"
                placeholder="XXXYY1234567"
                maxlength="12"
            />
        </div>

        <div v-if="error" style="color:red">{{ error }}</div>
        <div v-if="success" style="color:green">{{ success }}</div>

        <div class="mt-4 flex items-center justify-left">
            <PrimaryButton
                class="ms-4"
                :class="{ 'opacity-25': loading }"
                :disabled="loading"
            >
                {{ loading ? 'Submitting...' : 'Submit' }}
            </PrimaryButton>
        </div>
    </form>
</template>

<script setup>

import { ref, onMounted} from 'vue';
import axios from 'axios';
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import DateInput from "@/Components/DateInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head } from '@inertiajs/vue3';
import VueSelect from "vue3-select-component";

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
        // stages.value = response.data.data;
        stages.value = Object.entries(response.data.data).map(([value, label]) => ({
            value,
            label
        }));
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
