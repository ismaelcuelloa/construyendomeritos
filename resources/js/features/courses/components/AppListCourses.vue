<script setup lang="ts">
import AppCourse from '@/features/courses/components/AppCourse.vue';
import { type Course } from '@/types/project';

interface Props {
    courses?: Course[];
    isMyCoursesPage?: boolean;
    showCategory?: boolean;
}

withDefaults(defineProps<Props>(), {
    showCategory: true,
});
</script>

<template>
    <!-- Start Card Area -->
    <div class="row g-5">
        <div v-for="course in courses" :key="course.id" class="col-lg-4 col-md-6 col-12">
            <AppCourse :course="course" :isMyCoursesPage="isMyCoursesPage" :showCategory="showCategory" />
        </div>
    </div>
</template>

<style scoped></style>
