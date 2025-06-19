import { createWebHistory, createRouter } from "vue-router";
import SalesOrderForm from './ZohoPages/SalesOrderForm.vue'

const routes = [
    { path: '/zoho/salesorder', name: "SalesOrderForm", component: SalesOrderForm },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
