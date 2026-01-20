<script setup>
import { useLayout } from '@/Layouts/composables/layout'
import { computed, ref } from 'vue'

import AppFooter from '@/Layouts/AppFooter.vue'
import AppSidebar from '@/Layouts/AppSidebar.vue'
import AppTopbar from '@/Layouts/AppTopbar.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const { layoutConfig, layoutState, hideMobileMenu } = useLayout()

const containerClass = computed(() => ({
    'layout-overlay': layoutConfig.menuMode === 'overlay',
    'layout-static': layoutConfig.menuMode === 'static',
    'layout-overlay-active': layoutState.overlayMenuActive,
    'layout-mobile-active': layoutState.mobileMenuActive,
    'layout-static-inactive': layoutState.staticMenuInactive,
}));

// 🚀 Dynamic component logic
const currentView = ref('AppLayout')

// map names to actual components
const components = {
    AppLayout
}

</script>

<template>
    <div class="layout-wrapper" :class="containerClass">
        <AppTopbar @navigate="view => currentView = view" />
        <AppSidebar @navigate="view => currentView = view" />

        <div class="layout-main-container">
            <div class="layout-main">
                <!-- 🌟 Dynamic component -->
                <component :is="components[currentView]" />
            </div>

            <AppFooter />
        </div>

        <div
            class="layout-mask animate-fadein"
            @click="hideMobileMenu"
        />
    </div>

    <Toast />
</template>