import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface SeoData {
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
    price?: number;
    currency?: string;
    availability?: string;
}

export function useSeo() {
    const page = usePage();

    const defaultSeo = computed(() => page.props.seo || {});

    const generateMetaTags = (data: SeoData) => {
        const seo = { ...defaultSeo.value, ...data };

        return {
            title: seo.title,
            description: seo.description,
            keywords: seo.keywords,
            ogTitle: seo.title,
            ogDescription: seo.description,
            ogImage: seo.image,
            ogUrl: seo.url,
            ogType: seo.type || 'website',
            twitterCard: 'summary_large_image',
            twitterTitle: seo.title,
            twitterDescription: seo.description,
            twitterImage: seo.image,
            author: seo.author,
            publishedTime: seo.publishedTime,
            modifiedTime: seo.modifiedTime,
            section: seo.section,
            tags: seo.tags,
            price: seo.price,
            currency: seo.currency,
            availability: seo.availability,
        };
    };

    const generateStructuredData = (type: string, data: any) => {
        const baseData = {
            '@context': 'https://schema.org',
            '@type': type,
        };

        return JSON.stringify({ ...baseData, ...data });
    };

    return {
        generateMetaTags,
        generateStructuredData,
        defaultSeo,
    };
}
