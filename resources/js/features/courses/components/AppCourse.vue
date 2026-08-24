<script setup lang="ts">
import * as COURSE from '@/lib/course';
import { formatMoney } from '@/lib/utils';
import { type Course } from '@/types/project';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import DemoCountdown from '@/features/courses/components/DemoCountdown.vue';

import type { User } from '@/types';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user as User;

interface Props {
    course: Course;
    isMyCoursesPage?: boolean;
    showCategory?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showCategory: true,
});

const image_default = '/assets/images/others/thumbnail-placeholder.svg';
const image = computed(() => {
    const banner = props.course.metadata?.banner;
    if (banner && typeof banner === 'string' && banner.trim() !== '') {
        // Si ya es una URL absoluta
        if (banner.startsWith('http')) {
            return banner;
        }
        // Si es una ruta relativa, la convertimos a absoluta
        return window.location.origin + (banner.startsWith('/') ? banner : '/' + banner);
    }
    return image_default;
});

const url = (slug: string) => {
    return '/cursos/' + slug;
};

const isSubscribed = computed(() => {
    return COURSE.isSubscribed(props.course, user);
});

// Verificar si el curso es demo y obtener fecha de expiración
const demoInfo = computed(() => {
    if (!props.course.order_items || props.course.order_items.length === 0) {
        return null;
    }

    // Buscar un order_item con orden demo
    const demoItem = props.course.order_items.find(item => {
        return item.order && 
               item.order.status_id === 7 && // OrderStatus::DEMO
               item.order.demo_expires_at;
    });

    if (!demoItem || !demoItem.order) {
        return null;
    }

    // Verificar si aún no expiró
    const expiresAt = new Date(demoItem.order.demo_expires_at!);
    const now = new Date();

    if (now >= expiresAt) {
        return null; // Ya expiró
    }

    return {
        expiresAt: demoItem.order.demo_expires_at
    };
});
</script>

<template>
    <div class="course-card-premium">
        <div class="course-card-image">
            <Link :href="url(course.slug)">
                <img :src="image" alt="Card image" />
                <div class="course-overlay">
                    <div class="overlay-content">
                        <i class="feather-eye"></i>
                        <span>Ver detalles</span>
                    </div>
                </div>
            </Link>
            <div v-if="!isSubscribed" class="course-badge-price">
                {{ formatMoney(course.price) }}
            </div>
            <div v-else class="course-badge-subscribed">
                <i class="feather-check-circle"></i>
                <span>Inscrito</span>
            </div>
        </div>

        <div class="course-card-content">
            <div v-if="showCategory && course.category && course.category.title" class="course-category-tag">
                <i class="feather-folder"></i>
                <span>{{ course.category.title }}</span>
            </div>

            <h4 class="course-title">
                <Link :href="url(course.slug)">
                    {{ course.title }}
                </Link>
            </h4>

            <!-- Contador de demo si aplica -->
            <DemoCountdown v-if="demoInfo && isMyCoursesPage" :expiresAt="demoInfo.expiresAt" />

            <p class="course-description">{{ course.description }}</p>

            <div class="course-meta-info">
                <div class="meta-item">
                    <i class="feather-book"></i>
                    <span>{{ course.modules_count ?? 0 }} módulos</span>
                </div>
                <div class="meta-item">
                    <i class="feather-users"></i>
                    <span>{{ 350 + (course.subscriptions_count ?? 0) }} usuarios</span>
                </div>
            </div>

            <div class="course-card-footer">
                <template v-if="!isSubscribed">
                    <Link class="btn-course-action" :href="url(course.slug)">
                        <span>Adquirir este material</span>
                        <i class="feather-arrow-right"></i>
                    </Link>
                </template>
                <template v-else>
                    <Link :href="url(course.slug)" class="btn-course-enrolled">
                        <i class="feather-check-circle"></i>
                        <span v-if="isMyCoursesPage">Seguir aprendiendo</span>
                        <span v-else>Adquirido</span>
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.course-card-premium {
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    border: 2px solid rgba(19, 58, 84, 0.1);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.course-card-premium:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(19, 58, 84, 0.2);
    border-color: rgba(19, 58, 84, 0.3);
}

.course-card-image {
    position: relative;
    overflow: hidden;
    height: 240px;
}

.course-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.course-card-premium:hover .course-card-image img {
    transform: scale(1.1);
}

.course-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.course-card-premium:hover .course-overlay {
    opacity: 1;
}

.overlay-content {
    text-align: center;
    color: #ffffff;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.course-card-premium:hover .overlay-content {
    transform: translateY(0);
}

.overlay-content i {
    font-size: 48px;
    display: block;
    margin-bottom: 10px;
}

.overlay-content span {
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.course-badge-price {
    position: absolute;
    top: 16px;
    right: 16px;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    color: #ffffff;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.3);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.course-badge-subscribed {
    position: absolute;
    top: 16px;
    right: 16px;
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    color: #ffffff;
    padding: 8px 14px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.course-badge-subscribed i {
    font-size: 16px;
}

.course-card-content {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    flex: 1;
}

.course-category-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(19, 58, 84, 0.1);
    color: #133a54;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: fit-content;
    border: 1px solid rgba(19, 58, 84, 0.2);
}

.course-category-tag i {
    font-size: 14px;
}

.course-title {
    margin: 0;
    font-size: 20px;
    font-weight: 800;
    line-height: 1.3;
    color: #1a1a1a;
}

.course-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.course-title a:hover {
    color: #133a54;
}

.course-description {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: #666;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.course-meta-info {
    display: flex;
    gap: 20px;
    padding-top: 12px;
    border-top: 1px solid rgba(19, 58, 84, 0.1);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #666;
    font-weight: 600;
}

.meta-item i {
    color: #133a54;
    font-size: 16px;
}

.course-card-footer {
    margin-top: auto;
}

.btn-course-action {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    color: #ffffff;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.2);
}

.btn-course-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.3);
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
}

.btn-course-action i {
    font-size: 18px;
}

.btn-course-enrolled {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 14px 20px;
    background: #ffffff;
    color: #133a54;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
    border: 2px solid #133a54;
    box-shadow: 0 2px 8px rgba(19, 58, 84, 0.1);
}

.btn-course-enrolled:hover {
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.3);
}

.btn-course-enrolled i {
    font-size: 20px;
}
</style>
