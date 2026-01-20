<script setup>
import { useLayout } from '@/Layouts/composables/layout'
import { onBeforeUnmount, ref, watch, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppMenu from './AppMenu.vue'

const { layoutState, isDesktop, hasOpenOverlay } = useLayout()
const page = usePage()
const sidebarRef = ref(null)
let outsideClickListener = null

// Inertia current URL (reactive)
const currentPath = computed(() => page.url.value)

watch(
    currentPath,
    (newPath) => {
        if (isDesktop()) layoutState.activePath = null
        else layoutState.activePath = newPath

        layoutState.overlayMenuActive = false
        layoutState.mobileMenuActive = false
        layoutState.menuHoverActive = false
    },
    { immediate: true }
)

watch(hasOpenOverlay, (newVal) => {
    if (!isDesktop()) return

    newVal ? bindOutsideClickListener() : unbindOutsideClickListener()
})

const bindOutsideClickListener = () => {
    if (outsideClickListener) return

    outsideClickListener = (event) => {
        if (isOutsideClicked(event)) {
            layoutState.overlayMenuActive = false
        }
    }

    document.addEventListener('click', outsideClickListener)
}

const unbindOutsideClickListener = () => {
    if (!outsideClickListener) return

    document.removeEventListener('click', outsideClickListener)
    outsideClickListener = null
}

const isOutsideClicked = (event) => {
    const sidebar = sidebarRef.value
    const topbarButton = document.querySelector('.layout-menu-button')

    if (!sidebar) return true

    return !(
        sidebar === event.target ||
        sidebar.contains(event.target) ||
        topbarButton === event.target ||
        topbarButton?.contains(event.target)
    )
}

onBeforeUnmount(unbindOutsideClickListener)
</script>

<template>
    <div ref="sidebarRef" class="layout-sidebar">
        <AppMenu />
    </div>
</template>