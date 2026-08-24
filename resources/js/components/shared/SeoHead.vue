<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    title?: string;
    description?: string;
    image?: string;
    url?: string;
    type?: 'website' | 'article' | 'product';
    keywords?: string;
    author?: string;
    publishedTime?: string;
    modifiedTime?: string;
    section?: string;
    tags?: string[];
    structuredData?: string;
    noindex?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'website',
    noindex: false,
});

const defaultImage = computed(() => {
    return props.image || 'http://127.0.0.1:8000/assets/images/logo/logo SEO.png';
});

const robotsContent = computed(() => {
    if (props.noindex) {
        return 'noindex, nofollow';
    }
    return 'index, follow';
});
</script>

<template>
    <Head>
        <!-- Basic Meta Tags -->
        <title>{{ title }}</title>
        <meta name="description" :content="description" v-if="description" />
        <meta name="keywords" :content="keywords" v-if="keywords" />
        <meta name="author" :content="author" v-if="author" />
        <meta name="robots" :content="robotsContent" />
        <link rel="canonical" :href="url" v-if="url" />

        <!-- Open Graph Meta Tags -->
        <meta property="og:title" :content="title" v-if="title" />
        <meta property="og:description" :content="description" v-if="description" />
        <meta property="og:image" :content="defaultImage" />
        <meta property="og:url" :content="url" v-if="url" />
        <meta property="og:type" :content="type" />
        <meta property="og:site_name" content="Construyendo Méritos con Excelencia" />
        <meta property="og:locale" content="es_ES" />

        <!-- Article Specific -->
        <meta property="article:published_time" :content="publishedTime" v-if="publishedTime && type === 'article'" />
        <meta property="article:modified_time" :content="modifiedTime" v-if="modifiedTime && type === 'article'" />
        <meta property="article:section" :content="section" v-if="section && type === 'article'" />
        <template v-if="tags && type === 'article'">
            <meta property="article:tag" :content="tag" v-for="tag in tags" :key="tag" />
        </template>

        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="title" v-if="title" />
        <meta name="twitter:description" :content="description" v-if="description" />
        <meta name="twitter:image" :content="defaultImage" />

        <!-- Structured Data -->
        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-if="structuredData" v-html="structuredData"></component>
    </Head>
</template>
