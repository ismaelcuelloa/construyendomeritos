<script setup lang="ts">
import type { ModuleFile } from '@/types/project';

interface Props {
    isSubscribed: boolean;
    files: ModuleFile[];
}

const props = defineProps<Props>();

const getURL = (file: ModuleFile) => {
    return props.isSubscribed ? '/cursos/modulos/archivos/' + file.id : '#';
};
</script>

<template>
    <ul class="rbt-course-main-content liststyle">
        <li v-for="file in files" :key="file.id">
            <a :href="getURL(file)">
                <div class="course-content-left">
                    <i class="feather-file-text"></i> <span class="text">{{ file.title }}</span>
                </div>
                <div v-if="!isSubscribed" class="course-content-right">
                    <span class="course-lock"><i class="feather-lock"></i></span>
                </div>
            </a>
        </li>
    </ul>
</template>

<style scoped></style>
