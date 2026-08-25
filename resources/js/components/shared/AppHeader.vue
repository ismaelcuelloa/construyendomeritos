<script setup lang="ts">
import AppCart from '@/features/cart/components/AppCart.vue';
import * as Cart from '@/composables/useCart';
import { getUser, isAdmin, user } from '@/composables/useUser';
import type { BreadcrumbItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const mobileMenuOpen = ref(false);

const isSimulacros = computed(() => {
    if (typeof window !== 'undefined') {
        return window.location.hostname === (page.props as any).simulacrosDomain;
    }
    return false;
});
const mainHomeUrl = computed(() => {
    const mainDomain = (page.props as any).mainDomain;
    return mainDomain ? `https://${mainDomain}/` : '/';
});
const mainUrl = (path: string) => {
    const mainDomain = (page.props as any).mainDomain;
    return mainDomain ? `https://${mainDomain}${path}` : path;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
    if (mobileMenuOpen.value) {
        document.body.classList.add('popup-mobile-menu-active');
    } else {
        document.body.classList.remove('popup-mobile-menu-active');
    }
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
    document.body.classList.remove('popup-mobile-menu-active');
};

watch(
    () => page.props.auth,
    () => {
        auth.value = page.props.auth;
    },
);

onMounted(() => {
    getUser();
});
</script>

<template>
    <!-- Start Header Area -->
    <header class="rbt-header rbt-header-10">
        <div class="rbt-sticky-placeholder" style="height: 0px"></div>

        <!-- Start Header Top  -->

        <!-- End Header Top  -->
        <div class="rbt-header-wrapper header-space-betwween header-sticky">
            <div class="container-fluid">
                <div class="mainbar-row rbt-navigation-center align-items-center">
                    <div class="header-left rbt-header-content">
                        <div class="header-info">
                            <div class="logo logo-dark">
                                <a v-if="isSimulacros" :href="mainHomeUrl" class="logo-link">
                                    <img src="/assets/images/logo/logo-color.png" alt="Education Logo Images" />
                                </a>
                                <Link v-else href="/" class="logo-link">
                                    <img src="/assets/images/logo/logo-color.png" alt="Education Logo Images" />
                                </Link>
                            </div>

                            <div class="logo d-none logo-light">
                                <a v-if="isSimulacros" :href="mainHomeUrl">
                                    <img src="/assets/images/logo/logo-color.png" alt="Education Logo Images" />
                                </a>
                                <Link v-else href="/">
                                    <img src="/assets/images/logo/logo-color.png" alt="Education Logo Images" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="rbt-main-navigation d-none d-xl-block">
                        <nav class="mainmenu-nav">
                            <ul class="mainmenu">
                                <li class="with-megamenu has-menu-child-item position-static menu-item-open">
                                    <a v-if="isSimulacros" :href="mainHomeUrl">Inicio</a>
                                    <Link v-else href="/">Inicio</Link>
                                    <!-- Start Mega Menu  -->

                                    <!-- End Mega Menu  -->
                                </li>

                                <li class="with-megamenu has-menu-child-item">
                                    <a v-if="isSimulacros" :href="mainUrl('/cursos')">Materiales de Estudio</a>
                                    <Link v-else href="/cursos">Materiales de Estudio</Link>
                                </li>

                                <li v-if="auth.user" class="with-megamenu has-menu-child-item">
                                    <a v-if="isSimulacros" :href="mainUrl('/mis_cursos')">Mis Materiales</a>
                                    <Link v-else href="/mis_cursos">Mis Materiales</Link>
                                </li>

                                <li v-if="isAdmin()" class="with-megamenu has-menu-child-item">
                                    <a v-if="isSimulacros" :href="mainUrl('/admin')">Admin Panel</a>
                                    <Link v-else href="/admin">Admin Panel</Link>
                                </li>

                                <li v-if="!auth.user" class="with-megamenu has-menu-child-item">
                                    <a v-if="isSimulacros" :href="mainUrl('/login')">Iniciar Sesión</a>
                                    <Link v-else href="/login">Iniciar Sesión</Link>
                                </li>

                                <li v-if="!auth.user" class="with-megamenu has-menu-child-item">
                                    <a v-if="isSimulacros" :href="mainUrl('/register')">Registrarse</a>
                                    <Link v-else href="/register">Registrarse</Link>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    <div class="header-right">
                        <!-- Navbar Icons -->
                        <ul class="quick-access">
                            <li class="access-icon rbt-mini-cart">
                                <a @click="Cart.useCart().open()" class="rbt-cart-sidenav-activation rbt-round-btn" href="#">
                                    <i class="feather-shopping-cart"></i>
                                    <span class="rbt-cart-count">{{ Cart.useCart().totalItems() }}</span>
                                </a>
                            </li>

                            <li v-if="auth.user" class="account-access rbt-user-wrapper d-none d-xl-block">
                                <a href="#" class="user-trigger-btn">
                                    <div class="user-avatar-btn">
                                        <i class="feather-user"></i>
                                    </div>
                                    <span class="user-name-text">{{ user?.name }}</span>
                                </a>
                                <div class="rbt-user-menu-list-wrapper">
                                    <div class="inner">
                                        <div class="rbt-admin-profile">
                                            <div class="admin-thumbnail">
                                                <div class="avatar-placeholder">
                                                    <i class="feather-user"></i>
                                                </div>
                                            </div>
                                            <div class="admin-info">
                                                <span class="name">{{ user?.name }}</span>
                                                <span class="email">{{ user?.email }}</span>
                                            </div>
                                        </div>
                                        <ul class="user-list-wrapper">
                                            <li v-if="isAdmin()">
                                                <a v-if="isSimulacros" :href="mainUrl('/admin')"><i class="feather-shield"></i><span>Panel de Administración</span></a>
                                                <Link v-else href="/admin"><i class="feather-shield"></i><span>Panel de Administración</span></Link>
                                            </li>
                                            <li>
                                                <a v-if="isSimulacros" :href="mainUrl('/mis_cursos')"><i class="feather-book-open"></i><span>Mis Materiales</span></a>
                                                <Link v-else href="/mis_cursos"><i class="feather-book-open"></i><span>Mis Materiales</span></Link>
                                            </li>
                                            <li>
                                                <a v-if="isSimulacros" :href="mainUrl('/mis_compras')"><i class="feather-shopping-bag"></i><span>Mis Compras</span></a>
                                                <Link v-else href="/mis_compras"><i class="feather-shopping-bag"></i><span>Mis Compras</span></Link>
                                            </li>
                                            <li>
                                                <a v-if="isSimulacros" :href="mainUrl('/change-password')"><i class="feather-lock"></i><span>Cambiar Contraseña</span></a>
                                                <Link v-else href="/change-password"><i class="feather-lock"></i><span>Cambiar Contraseña</span></Link>
                                            </li>
                                        </ul>
                                        <hr class="mt--10 mb--10" />
                                        <ul class="user-list-wrapper">
                                            <li>
                                                <form :action="isSimulacros ? mainUrl('/logout') : '/logout'" method="post" class="logout-btn">
                                                    <button type="submit" class="logout-btn">
                                                        <i class="feather-log-out"></i>
                                                        <span>Cerrar Sesión</span>
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>

                            <li v-if="auth.user" class="access-icon rbt-user-wrapper d-block d-xl-none">
                                <a class="rbt-round-btn" href="#"><i class="feather-user"></i></a>
                                <div class="rbt-user-menu-list-wrapper">
                                    <div class="inner">
                                        <div class="rbt-admin-profile">
                                            <div class="admin-thumbnail">
                                                <div class="avatar-placeholder">
                                                    <i class="feather-user"></i>
                                                </div>
                                            </div>
                                            <div class="admin-info">
                                                <span class="name">{{ user?.name }}</span>
                                                <span class="email">{{ user?.email }}</span>
                                            </div>
                                        </div>
                                        <ul class="user-list-wrapper">
                                            <li v-if="isAdmin()">
                                                <a v-if="isSimulacros" :href="mainUrl('/admin')"><i class="feather-shield"></i><span>Panel de Administración</span></a>
                                                <Link v-else href="/admin"><i class="feather-shield"></i><span>Panel de Administración</span></Link>
                                            </li>
                                            <li>
                                                <a v-if="isSimulacros" :href="mainUrl('/mis_cursos')"><i class="feather-book-open"></i><span>Mis Materiales</span></a>
                                                <Link v-else href="/mis_cursos"><i class="feather-book-open"></i><span>Mis Materiales</span></Link>
                                            </li>
                                            <li>
                                                <a v-if="isSimulacros" :href="mainUrl('/mis_compras')"><i class="feather-shopping-bag"></i><span>Mis Compras</span></a>
                                                <Link v-else href="/mis_compras"><i class="feather-shopping-bag"></i><span>Mis Compras</span></Link>
                                            </li>
                                            <li>
                                                <a v-if="isSimulacros" :href="mainUrl('/change-password')"><i class="feather-lock"></i><span>Cambiar Contraseña</span></a>
                                                <Link v-else href="/change-password"><i class="feather-lock"></i><span>Cambiar Contraseña</span></Link>
                                            </li>
                                        </ul>
                                        <hr class="mt--10 mb--10" />
                                        <ul class="user-list-wrapper">
                                            <li>
                                                <form :action="isSimulacros ? mainUrl('/logout') : '/logout'" method="post" class="logout-btn">
                                                    <button type="submit" class="logout-btn">
                                                        <i class="feather-log-out"></i>
                                                        <span>Cerrar Sesión</span>
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Start Mobile-Menu-Bar -->
                    <div class="mobile-menu-bar d-block d-xl-none">
                        <div class="hamberger">
                            <button @click="toggleMobileMenu" class="hamberger-button rbt-round-btn">
                                <i class="feather-menu"></i>
                            </button>
                        </div>
                    </div>
                    <!-- End Mobile-Menu-Bar -->
                </div>
            </div>
        </div>
        <!-- Start Search Dropdown  -->
        <div class="rbt-search-dropdown">
            <div class="wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <form action="#">
                            <input type="text" placeholder="What are you looking for?" />
                            <div class="submit-btn">
                                <a class="rbt-btn btn-gradient btn-md" href="#">Search</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="rbt-separator-mid">
                    <hr class="rbt-separator m-0" />
                </div>

                <div class="row g-4 pt--30 pb--60">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h5 class="rbt-title-style-2">Our Top Course</h5>
                        </div>
                    </div>

                    <!-- Start Single Card  -->
                    <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                        <div class="rbt-card variation-01 rbt-hover">
                            <div class="rbt-card-img">
                                <a href="course-details.html">
                                    <img src="/assets/images/logo/logo-color.png" alt="Card image" />
                                </a>
                            </div>
                            <div class="rbt-card-body">
                                <h5 class="rbt-card-title"><a href="course-details.html">React Js</a></h5>
                                <div class="rbt-review">
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="rating-count"> (15 Reviews)</span>
                                </div>
                                <div class="rbt-card-bottom">
                                    <div class="rbt-price">
                                        <span class="current-price">$15</span>
                                        <span class="off-price">$25</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Single Card  -->

                    <!-- Start Single Card  -->
                    <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                        <div class="rbt-card variation-01 rbt-hover">
                            <div class="rbt-card-img">
                                <a href="course-details.html">
                                    <img src="/assets/images/logo/logo-color.png" alt="Card image" />
                                </a>
                            </div>
                            <div class="rbt-card-body">
                                <h5 class="rbt-card-title"><a href="course-details.html">Java Program</a></h5>
                                <div class="rbt-review">
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="rating-count"> (15 Reviews)</span>
                                </div>
                                <div class="rbt-card-bottom">
                                    <div class="rbt-price">
                                        <span class="current-price">$10</span>
                                        <span class="off-price">$40</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Single Card  -->

                    <!-- Start Single Card  -->
                    <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                        <div class="rbt-card variation-01 rbt-hover">
                            <div class="rbt-card-img">
                                <a href="course-details.html">
                                    <img src="/assets/images/logo/logo-color.png" alt="Card image" />
                                </a>
                            </div>
                            <div class="rbt-card-body">
                                <h5 class="rbt-card-title"><a href="course-details.html">Web Design</a></h5>
                                <div class="rbt-review">
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="rating-count"> (15 Reviews)</span>
                                </div>
                                <div class="rbt-card-bottom">
                                    <div class="rbt-price">
                                        <span class="current-price">$10</span>
                                        <span class="off-price">$20</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Single Card  -->

                    <!-- Start Single Card  -->
                    <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                        <div class="rbt-card variation-01 rbt-hover">
                            <div class="rbt-card-img">
                                <a href="course-details.html">
                                    <img src="/assets/images/logo/logo-color.png" alt="Card image" />
                                </a>
                            </div>
                            <div class="rbt-card-body">
                                <h5 class="rbt-card-title"><a href="course-details.html">Web Design</a></h5>
                                <div class="rbt-review">
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="rating-count"> (15 Reviews)</span>
                                </div>
                                <div class="rbt-card-bottom">
                                    <div class="rbt-price">
                                        <span class="current-price">$20</span>
                                        <span class="off-price">$40</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Single Card  -->
                </div>
            </div>
        </div>
        <!-- End Search Dropdown  -->

        <a class="rbt-close_side_menu" href="javascript:void(0);"></a>
    </header>

    <!-- Mobile Menu -->
    <div class="popup-mobile-menu" :class="{ active: mobileMenuOpen }">
        <div class="inner-wrapper">
            <div class="inner-top">
                <div class="content">
                    <div class="logo">
                        <a v-if="isSimulacros" :href="mainHomeUrl">
                            <img src="/assets/images/logo/logo-color.png" alt="Education Logo" />
                        </a>
                        <Link v-else @click="closeMobileMenu" href="/">
                            <img src="/assets/images/logo/logo-color.png" alt="Education Logo" />
                        </Link>
                    </div>
                    <div class="rbt-btn-close">
                        <button @click="closeMobileMenu" class="close-button rbt-round-btn">
                            <i class="feather-x"></i>
                        </button>
                    </div>
                </div>
                <p class="description">Prepárate para triunfar en tus exámenes</p>
                <ul class="navbar-top-left rbt-information-list justify-content-start">
                    <li v-if="auth.user">
                        <div class="mobile-user-info">
                            <div class="user-avatar-mobile">
                                <i class="feather-user"></i>
                            </div>
                            <div class="user-details-mobile">
                                <span class="user-name-mobile">{{ user?.name }}</span>
                                <span class="user-email-mobile">{{ user?.email }}</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <nav class="mainmenu-nav">
                <ul class="mainmenu">
                    <li>
                        <a v-if="isSimulacros" :href="mainHomeUrl">Inicio</a>
                        <Link v-else @click="closeMobileMenu" href="/">Inicio</Link>
                    </li>
                    <li>
                        <a v-if="isSimulacros" :href="mainUrl('/cursos')">Materiales de Estudio</a>
                        <Link v-else @click="closeMobileMenu" href="/cursos">Materiales de Estudio</Link>
                    </li>
                    <li v-if="auth.user">
                        <a v-if="isSimulacros" :href="mainUrl('/mis_cursos')">Mis Materiales</a>
                        <Link v-else @click="closeMobileMenu" href="/mis_cursos">Mis Materiales</Link>
                    </li>
                    <li v-if="isAdmin()">
                        <a v-if="isSimulacros" :href="mainUrl('/admin')">Admin Panel</a>
                        <Link v-else @click="closeMobileMenu" href="/admin">Admin Panel</Link>
                    </li>
                    <li v-if="!auth.user">
                        <a v-if="isSimulacros" :href="mainUrl('/login')">Iniciar Sesión</a>
                        <Link v-else @click="closeMobileMenu" href="/login">Iniciar Sesión</Link>
                    </li>
                    <li v-if="!auth.user">
                        <a v-if="isSimulacros" :href="mainUrl('/register')">Registrarse</a>
                        <Link v-else @click="closeMobileMenu" href="/register">Registrarse</Link>
                    </li>
                    <li v-if="auth.user">
                        <form :action="isSimulacros ? mainUrl('/logout') : '/logout'" method="post" class="logout-mobile-btn">
                            <button type="submit" class="logout-mobile-btn">
                                <i class="feather-log-out"></i>
                                <span>Cerrar Sesión</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <AppCart />
</template>

<style scoped>
/* Logo Hover Effect - Scale Animation (igual que footer) */
.logo-link {
    display: inline-block;
    transition: transform 0.3s ease;
}

.logo-link:hover {
    transform: scale(1.05);
}

/* User Menu Styles */
.avatar-placeholder {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 16px;
}

:deep(.rbt-user-menu-list-wrapper) {
    min-width: 220px !important;
    width: 220px !important;
    max-height: 70vh !important;
    overflow-y: auto !important;
    top: calc(100% + 5px) !important;
    right: 0 !important;
    left: auto !important;
    position: absolute !important;
}

:deep(.rbt-user-menu-list-wrapper .inner) {
    padding: 12px !important;
}

:deep(.rbt-admin-profile) {
    padding-bottom: 10px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

:deep(.admin-thumbnail) {
    margin-right: 0 !important;
    flex-shrink: 0 !important;
}

:deep(.admin-info) {
    flex: 1 !important;
    min-width: 0 !important;
}

:deep(.admin-info .name) {
    font-size: 13px;
    font-weight: 700;
    color: #151515;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}

:deep(.admin-info .email) {
    font-size: 10px;
    color: #666;
    display: block;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

:deep(.user-list-wrapper) {
    margin: 6px 0 !important;
}

:deep(.user-list-wrapper li) {
    margin: 0 !important;
}

:deep(.user-list-wrapper li a),
:deep(.logout-btn) {
    padding: 7px 8px !important;
    font-size: 12px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

:deep(.user-list-wrapper li a i),
:deep(.logout-btn i) {
    font-size: 14px !important;
    width: 16px !important;
    flex-shrink: 0 !important;
}

:deep(.user-list-wrapper li a span),
:deep(.logout-btn span) {
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

:deep(.inner hr) {
    margin: 6px 0 !important;
}

:deep(.logout-btn) {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    color: inherit;
    transition: all 0.3s ease;
}

:deep(.logout-btn:hover) {
    color: #dc2626;
}

form.logout-btn {
    width: 100%;
    margin: 0;
}

form.logout-btn button.logout-btn {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    cursor: pointer;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

form.logout-btn button.logout-btn:hover {
    color: #dc2626;
}

/* Asegurar que el wrapper del usuario tenga position relative */
:deep(.rbt-user-wrapper) {
    position: relative !important;
}

/* Estilo del botón de usuario en el header */
.user-trigger-btn {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 8px 16px !important;
    border-radius: 25px !important;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(245, 228, 44, 0.03) 100%) !important;
    border: 2px solid rgba(19, 58, 84, 0.15) !important;
    transition: all 0.3s ease !important;
}

.user-trigger-btn:hover {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(245, 228, 44, 0.05) 100%) !important;
    border-color: rgba(19, 58, 84, 0.3) !important;
    transform: translateY(-1px);
}

.user-avatar-btn {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 16px;
    flex-shrink: 0;
}

.user-avatar-btn i {
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    margin: 0;
}

.user-name-text {
    font-size: 14px;
    font-weight: 600;
    color: #151515;
    white-space: nowrap;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-trigger-btn:hover .user-name-text {
    color: #f5e42c !important;
}

/* Mobile Menu Styles */
.popup-mobile-menu {
    position: fixed;
    top: 0;
    right: -400px;
    width: 350px;
    height: 100%;
    background: #ffffff;
    z-index: 9999;
    transition: right 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
}

.popup-mobile-menu.active {
    right: 0;
}

.popup-mobile-menu .inner-wrapper {
    padding: 30px;
}

.popup-mobile-menu .inner-top {
    margin-bottom: 30px;
}

.popup-mobile-menu .content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.popup-mobile-menu .logo img {
    max-width: 150px;
}

.popup-mobile-menu .rbt-btn-close button {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1), rgba(245, 228, 44, 0.05));
    border: 2px solid rgba(19, 58, 84, 0.2);
    color: #133a54;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.popup-mobile-menu .rbt-btn-close button:hover {
    background: linear-gradient(135deg, #133a54, #f5e42c);
    color: white;
    transform: rotate(90deg);
}

.popup-mobile-menu .description {
    font-size: 13px;
    color: #666;
    margin-bottom: 15px;
}

.mobile-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05), rgba(245, 228, 44, 0.03));
    border-radius: 12px;
    border: 2px solid rgba(19, 58, 84, 0.1);
}

.user-avatar-mobile {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #133a54, #f5e42c);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.user-details-mobile {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.user-name-mobile {
    font-size: 14px;
    font-weight: 700;
    color: #151515;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-email-mobile {
    font-size: 12px;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.popup-mobile-menu .mainmenu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.popup-mobile-menu .mainmenu li {
    margin: 0;
    border-bottom: 1px solid rgba(19, 58, 84, 0.1);
}

.popup-mobile-menu .mainmenu li a,
.logout-mobile-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 10px;
    color: #151515;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    width: 100%;
    background: none;
    border: none;
    text-align: left;
    cursor: pointer;
}

form.logout-mobile-btn {
    display: flex;
    align-items: center;
    margin: 0;
    width: 100%;
}

form.logout-mobile-btn button.logout-mobile-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 10px;
    color: #151515;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    width: 100%;
    background: none;
    border: none;
    text-align: left;
    cursor: pointer;
}

.popup-mobile-menu .mainmenu li a:hover,
.logout-mobile-btn:hover,
form.logout-mobile-btn button.logout-mobile-btn:hover {
    color: #f5e42c !important;
    padding-left: 20px;
}

.logout-mobile-btn i {
    font-size: 18px;
}

/* Change Password Button */
.change-password-btn {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 12px 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #3e3e3e;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.change-password-btn i {
    font-size: 16px;
    color: #133a54;
    transition: all 0.3s ease;
}

.change-password-btn:hover {
    background: rgba(19, 58, 84, 0.08);
    color: #f5e42c;
    padding-left: 24px;
}

.change-password-btn:hover i {
    transform: scale(1.1);
    color: #f5e42c;
}

/* Overlay cuando el menú móvil está abierto */
:deep(body.popup-mobile-menu-active::before) {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9998;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@media (max-width: 400px) {
    .popup-mobile-menu {
        width: 90%;
        right: -90%;
    }
}

:deep(.rbt-header .mainmenu-nav .mainmenu li.has-dropdown .submenu li a:hover),
:deep(.rbt-header .mainmenu-nav .mainmenu li.with-megamenu .rbt-megamenu .wrapper .mega-menu-item li a:hover) {
    color: #f5e42c !important;
}
</style>
