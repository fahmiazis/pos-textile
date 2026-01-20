<script setup>
import { ref, computed } from 'vue';
import AppMenuItem from './AppMenuItem.vue';
import { usePage, Link } from '@inertiajs/vue3';

const { url, component } = usePage();

const model = computed(() => [
    {
        label: 'Home',
        items: [
            {
                label: 'Dashboard',
                icon: 'pi pi-fw pi-home',
                to: '/'
            }
        ]
    },
    {
        label: 'Master Data',
        path: '/masterdata',
        items: [
            {
                label: 'Customer & Supplier',
                icon: 'pi pi-fw pi-id-card',
                path: '/customer',
                items: [
                    {
                        label: 'Master Customer',
                        icon: 'pi pi-fw pi-id-card',
                        to: '/mcustomers'
                    },
                    {
                        label: 'Supplier',
                        icon: 'pi pi-fw pi-address-book',
                        to: '/msuppliers'
                    },
                    {
                        label: 'Store',
                        icon: 'pi pi-fw pi-building-columns',
                        to: '/mstores'
                    },
                ]
            },
            {
                label: 'Product',
                icon: 'pi pi-fw pi-box',
                path: '/product',
                items: [
                    {
                        label: 'Master Product',
                        icon: 'pi pi-fw pi-box',
                        to: '/mproducts'
                    },
                    {
                        label: 'Master Pricing Product',
                        icon: 'pi pi-fw pi-dollar',
                        to: '/mpricing'
                    },
                    {
                        label: 'Master Unit/UOM',
                        icon: 'pi pi-fw pi-pound',
                        to: '/munit'
                    },
                    {
                        label: 'Master Brand',
                        icon: 'pi pi-fw pi-clipboard',
                        to: '/mbrand'
                    },
                    {
                        label: 'Master Category',
                        icon: 'pi pi-fw pi-inbox',
                        to: '/mcategory'
                    },
                ]
            },
            
            
        ]
    }
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator" ></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>