<template>
    <div class="bg-gray-50 p-4 rounded-lg w-full max-w-sm ml-auto space-y-4 shadow-sm">
        <div class="flex justify-between">
            <span class="font-medium">Sub Total</span>
            <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
        </div>

<!--        <div class="flex justify-between items-center">-->
<!--            <label for="discount" class="font-medium">Discount</label>-->
<!--            <div class="flex items-center space-x-1">-->
<!--                <input-->
<!--                    type="number"-->
<!--                    id="discount"-->
<!--                    class="w-16 text-right border rounded-md px-2 py-1 text-sm"-->
<!--                    v-model.number="discount"-->
<!--                />-->
<!--                <span class="text-sm">%</span>-->
<!--            </div>-->
<!--        </div>-->

<!--        <div class="flex justify-between items-center">-->
<!--            <label for="shipping" class="font-medium">Shipping Charges</label>-->
<!--            <input-->
<!--                type="number"-->
<!--                id="shipping"-->
<!--                class="w-24 text-right border rounded-md px-2 py-1 text-sm"-->
<!--                v-model.number="shippingCharges"-->
<!--            />-->
<!--        </div>-->

<!--        <div class="flex justify-between items-center">-->
<!--            <label for="adjustment" class="font-medium">Adjustment</label>-->
<!--            <input-->
<!--                type="number"-->
<!--                id="adjustment"-->
<!--                class="w-24 text-right border rounded-md px-2 py-1 text-sm"-->
<!--                v-model.number="adjustment"-->
<!--            />-->
<!--        </div>-->

        <div class="border-t pt-4 flex justify-between text-lg font-semibold">
            <span>Total ($)</span>
            <span>${{ total.toFixed(2) }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, defineProps, defineEmits } from 'vue'

const props = defineProps({
    subtotal: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(['update:total'])

const discount = ref(0)
const shippingCharges = ref(0)
const adjustment = ref(0)

const total = computed(() => {
    const discountAmount = props.subtotal * (discount.value / 100)
    return (
        props.subtotal -
        discountAmount +
        (shippingCharges.value || 0) +
        (adjustment.value || 0)
    )
})

// emit updated total when values change
watch([discount, shippingCharges, adjustment, () => props.subtotal], () => {
    emit('update:total', total.value)
})
</script>
