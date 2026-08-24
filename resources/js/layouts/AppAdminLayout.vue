<script setup lang="ts">
import AppSidebar from '@/features/admin/components/AppSidebar.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AppSidebarLayout from '@/layouts/admin/AppSidebarLayout.vue';

interface Props {
    title: string;
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <div class="rbt-dashboard-area admin-dashboard-area">
            <div class="container-fluid" style="max-width: 1800px">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row g-4">
                            <div class="col-lg-2 col-xl-2">
                                <AppSidebar />
                            </div>

                            <div class="col-lg-10 col-xl-10">
                                <div class="admin-content-wrapper">
                                    <slot></slot>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <AppSidebarLayout />
    </AppLayout>
</template>

<style scoped>
.admin-dashboard-area {
    padding: 40px 0;
    min-height: calc(100vh - 200px);
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.02) 0%, rgba(26, 90, 128, 0.01) 100%);
}

.admin-content-wrapper {
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .admin-dashboard-area {
        padding: 24px 0;
    }
}
</style>
