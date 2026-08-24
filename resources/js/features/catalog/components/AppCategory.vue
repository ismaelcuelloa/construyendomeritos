<script setup lang="ts">
import { Category } from '@/types/project';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    category: Category;
}

const props = defineProps<Props>();

const image_default = '/assets/images/others/thumbnail-placeholder.svg';
const image = ref(props.category.image ? props.category.image.url : image_default);

const url = (slug: string) => {
    return '/categorias/' + slug;
};
</script>

<template>
    <div class="rbt-card variation-01 rbt-hover category-card-premium">
        <div class="rbt-card-img">
            <Link :href="url(category.slug)">
                <img :src="image" alt="Card image" />
                <div class="image-overlay">
                    <div class="overlay-badge">
                        <i class="feather-folder"></i>
                    </div>
                </div>
            </Link>
        </div>
        <div class="rbt-card-body">
            <div class="category-badge">
                <i class="feather-layers"></i>
                <span>{{ category.courses_count ?? category.courses?.length ?? 0 }} Materiales</span>
            </div>

            <h4 class="rbt-card-title">
                <Link :href="url(category.slug)">
                    {{ category.title }}
                </Link>
            </h4>

            <p class="rbt-card-text">{{ category.description }}</p>

            <div class="card-footer-premium">
                <Link class="btn-explore" :href="url(category.slug)">
                    <span>Explorar</span>
                    <i class="feather-arrow-right"></i>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.category-card-premium {
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.rbt-card.variation-01 {
    border-radius: 20px;
    overflow: hidden;
    background: #ffffff;
    border: 2px solid transparent;
    box-shadow: 0 8px 30px rgba(19, 58, 84, 0.08);
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
}

.rbt-card.variation-01::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 20px;
    padding: 2px;
    background: linear-gradient(135deg, #133a54, #1a5a80);
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
}

.rbt-card.variation-01.rbt-hover:hover {
    box-shadow: 0 20px 60px rgba(19, 58, 84, 0.2);
    transform: translateY(-8px);
}

.rbt-card.variation-01.rbt-hover:hover::before {
    opacity: 1;
}

.rbt-card-img {
    position: relative;
    overflow: hidden;
    height: 220px;
}

.rbt-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.rbt-card.variation-01.rbt-hover:hover .rbt-card-img img {
    transform: scale(1.08);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.6) 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rbt-card.variation-01.rbt-hover:hover .image-overlay {
    opacity: 1;
}

.overlay-badge {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #133a54, #1a5a80);
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(0.8);
    transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}

.rbt-card.variation-01.rbt-hover:hover .overlay-badge {
    transform: scale(1);
}

.overlay-badge i {
    color: #ffffff;
    font-size: 24px;
}

.rbt-card-body {
    padding: 28px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%);
    color: #133a54;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
    width: fit-content;
    border: 1px solid rgba(19, 58, 84, 0.2);
    transition: all 0.3s ease;
}

.category-badge i {
    font-size: 14px;
}

.rbt-card.variation-01.rbt-hover:hover .category-badge {
    background: linear-gradient(135deg, #133a54, #1a5a80);
    color: #ffffff;
    border-color: transparent;
    transform: translateX(4px);
}

.rbt-card-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #151515;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
    line-height: 1.3;
    transition: color 0.3s ease;
}

.rbt-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.rbt-card.variation-01.rbt-hover:hover .rbt-card-title a {
    background: linear-gradient(90deg, #133a54 0%, #1a5a80 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.rbt-card-text {
    color: #666;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 24px;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-footer-premium {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid rgba(19, 58, 84, 0.1);
}

.btn-explore {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.25);
}

.btn-explore::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition:
        width 0.6s ease,
        height 0.6s ease;
}

.btn-explore:hover::before {
    width: 300px;
    height: 300px;
}

.btn-explore:hover {
    transform: translateX(4px);
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.35);
}

.btn-explore span {
    position: relative;
    z-index: 1;
}

.btn-explore i {
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
    font-size: 16px;
}

.btn-explore:hover i {
    transform: translateX(4px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .rbt-card-img {
        height: 200px;
    }

    .rbt-card-title {
        font-size: 1.2rem;
    }

    .rbt-card-body {
        padding: 20px;
    }
}
</style>
