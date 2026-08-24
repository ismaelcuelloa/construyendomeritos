<script setup lang="ts">
import AppListCourses from '@/features/courses/components/AppListCourses.vue';

import { type Course } from '@/types/project';

interface Props {
    courses?: Course[];
}

defineProps<Props>();
</script>

<template>
    <!-- Start Course Area -->
    <div class="rbt-course-area bg-color-white mb--20">
        <div class="container">
            <div class="row mb--60">
                <div class="col-lg-12">
                    <div class="section-title text-center">
                        <span class="subtitle bg-secondary-opacity">Top Popular Course</span>
                        <h2 class="title">
                            Histudy Course student <br />
                            can join with us.
                        </h2>
                    </div>
                </div>
            </div>
            <!-- Start Card Area -->
            <AppListCourses :courses="courses" />
            <!-- End Card Area -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="load-more-btn mt--60 text-center">
                        <a class="rbt-btn btn-gradient btn-lg hover-icon-reverse" href="#">
                            <span class="icon-reverse-wrapper">
                                <span class="btn-text">Load More Course (40)</span>
                                <span class="btn-icon"><i class="feather-arrow-right"></i></span>
                                <span class="btn-icon"><i class="feather-arrow-right"></i></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Course Area -->
</template>

<style scoped></style>
