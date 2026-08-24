<script setup lang="ts">
import type { Paginated } from '@/types/project';
import { computed } from 'vue';

interface Props {
    paginated: Paginated<any>;
    pages?: number;
}

export interface PageChangeEvent {
    page: number;
    url: string | null;
}

const props = withDefaults(defineProps<Props>(), {
    pages: 3,
});

const emit = defineEmits<{
    pageChanged: [event: PageChangeEvent]
}>();

const pagesRange = computed(() => {
    const currentPage = props.paginated.current_page;
    const lastPage = props.paginated.last_page;
    const range = props.pages;

    let start = Math.max(currentPage - Math.floor(range / 2), 1);
    let end = start + range - 1;

    if (end > lastPage) {
        end = lastPage;
        start = Math.max(end - range + 1, 1);
    }

    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const getUrlForPage = (pageNumber: number): string | null => {
    return props.paginated.links.find(link =>
        link.label === pageNumber.toString()
    )?.url || null;
};

const handlePageClick = (pageNumber: number) => {
    if (pageNumber !== props.paginated.current_page) {
        emit('pageChanged', {
            page: pageNumber,
            url: getUrlForPage(pageNumber)
        });
    }
};
</script>

<template>
    <nav v-if="paginated.last_page > 1">
        <ul class="rbt-pagination">
            <!-- Botón Anterior -->
            <li>
                <a
                    href="#"
                    @click.prevent="handlePageClick(paginated.current_page - 1)"
                    :class="{ 'disabled': !paginated.prev_page_url }"
                    aria-label="Previous"
                >
                    <i class="feather-chevron-left"></i>
                </a>
            </li>

            <!-- Primera página si no está en el rango -->
            <li v-if="pagesRange[0] > 1">
                <a
                    href="#"
                    @click.prevent="handlePageClick(1)"
                    :class="{ 'active': paginated.current_page === 1 }"
                >
                    1
                </a>
            </li>

            <!-- Elipsis si hay salto -->
            <li v-if="pagesRange[0] > 2">
                <span>...</span>
            </li>

            <!-- Rango de páginas -->
            <li v-for="pageNumber in pagesRange" :key="pageNumber">
                <a
                    href="#"
                    @click.prevent="handlePageClick(pageNumber)"
                    :class="{ 'active': paginated.current_page === pageNumber }"
                >
                    {{ pageNumber }}
                </a>
            </li>

            <!-- Elipsis si hay salto al final -->
            <li v-if="pagesRange[pagesRange.length - 1] < paginated.last_page - 1">
                <span>...</span>
            </li>

            <!-- Última página si no está en el rango -->
            <li v-if="pagesRange[pagesRange.length - 1] < paginated.last_page">
                <a
                    href="#"
                    @click.prevent="handlePageClick(paginated.last_page)"
                    :class="{ 'active': paginated.current_page === paginated.last_page }"
                >
                    {{ paginated.last_page }}
                </a>
            </li>

            <!-- Botón Siguiente -->
            <li>
                <a
                    href="#"
                    @click.prevent="handlePageClick(paginated.current_page + 1)"
                    :class="{ 'disabled': !paginated.next_page_url }"
                    aria-label="Next"
                >
                    <i class="feather-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
</template>
