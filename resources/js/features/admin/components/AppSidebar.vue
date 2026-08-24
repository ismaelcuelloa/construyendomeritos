<script setup lang="ts">
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user as User;

const mainPages = [
    {
        id: 1,
        url: '/admin',
        name: 'Dashboard',
        icon: 'feather-home',
    },
    {
        id: 2,
        url: '/admin/usuarios',
        name: 'Usuarios',
        icon: 'feather-user',
    },
    {
        id: 3,
        url: '/admin/categorias',
        name: 'Categorías',
        icon: 'feather-folder-minus',
    },
    {
        id: 4,
        url: '/admin/cursos',
        name: 'Cursos',
        icon: 'feather-book-open',
    },
    {
        id: 5,
        url: '/admin/ordenes',
        name: 'Ordenes',
        icon: 'feather-shopping-cart',
    },
];

const isActive = (url: string) => {
    if (url === '/admin') {
        return page.url === '/admin';
    }
    return page.url.startsWith(url);
};

const logout = () => {
    router.post(
        route('logout'),
        {},
        {
            onSuccess: () => {
                router.flushAll();
                window.location.href = '/';
            },
        },
    );
};
</script>

<template>
    <div class="admin-sidebar sticky-top">
        <div class="sidebar-inner">
            <!-- User Profile Card -->
            <div class="user-profile-card">
                <div class="user-avatar">
                    <i class="feather-user"></i>
                </div>
                <div class="user-info">
                    <h6 class="user-name">{{ user.name }}</h6>
                    <p class="user-email">{{ user.email }}</p>
                </div>
            </div>

            <!-- Main Navigation -->
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h6 class="nav-section-title">Principal</h6>
                    <ul class="nav-list">
                        <li v-for="item in mainPages" :key="item.id" class="nav-item">
                            <Link :href="item.url" :class="['nav-link', { active: isActive(item.url) }]">
                                <span class="nav-icon">
                                    <i :class="item.icon"></i>
                                </span>
                                <span class="nav-label">{{ item.name }}</span>
                                <span v-if="isActive(item.url)" class="nav-indicator"></span>
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h6 class="nav-section-title">Cuenta</h6>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <Link href="/admin/settings" :class="['nav-link', { active: isActive('/admin/settings') }]">
                                <span class="nav-icon">
                                    <i class="feather-settings"></i>
                                </span>
                                <span class="nav-label">Configuración</span>
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link href="/change-password" class="nav-link">
                                <span class="nav-icon">
                                    <i class="feather-lock"></i>
                                </span>
                                <span class="nav-label">Cambiar Contraseña</span>
                            </Link>
                        </li>
                        <li class="nav-item">
                            <a href="#" @click.prevent="logout" class="nav-link">
                                <span class="nav-icon">
                                    <i class="feather-log-out"></i>
                                </span>
                                <span class="nav-label">Salir</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.admin-sidebar {
    background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
    border: 2px solid rgba(19, 58, 84, 0.15);
    border-radius: 20px;
    overflow: hidden;
    height: fit-content;
    box-shadow:
        0 8px 24px rgba(0, 0, 0, 0.1),
        0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    transition: all 0.3s ease;
}

.admin-sidebar:hover {
    box-shadow:
        0 12px 32px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    transform: translateY(-2px);
}

.sidebar-inner {
    display: flex;
    flex-direction: column;
}

/* User Profile Card */
.user-profile-card {
    padding: 24px;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    overflow: hidden;
}

.user-profile-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.2), transparent);
    border-radius: 50%;
}

.user-avatar {
    flex-shrink: 0;
    width: 56px;
    height: 56px;
    border-radius: 14px;
    overflow: hidden;
    border: 3px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
}

.user-avatar:hover {
    transform: scale(1.05);
    border-color: rgba(255, 255, 255, 0.6);
}

.user-avatar i {
    font-size: 28px;
    color: white;
}

.user-info {
    flex: 1;
    min-width: 0;
    position: relative;
    z-index: 1;
}

.user-name {
    font-size: 16px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 4px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    letter-spacing: -0.3px;
}

.user-email {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 500;
}

/* Navigation */
.sidebar-nav {
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.nav-section {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.nav-section-title {
    font-size: 12px;
    font-weight: 800;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0 0 10px 0;
    padding: 0 12px;
}

.nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    color: #555;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(19, 58, 84, 0.15), transparent);
    transition: left 0.5s ease;
}

.nav-link:hover {
    color: #1a5a80;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.08) 0%, rgba(26, 90, 128, 0.05) 100%);
    transform: translateX(4px);
}

.nav-link:hover::before {
    left: 100%;
}

.nav-link.active {
    color: white;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    box-shadow: 0 6px 18px rgba(19, 58, 84, 0.3);
    transform: translateX(0);
}

.nav-link.active::before {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
}

.nav-link.active:hover::before {
    left: 100%;
}

.nav-icon {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.nav-label {
    flex: 1;
    min-width: 0;
    letter-spacing: -0.2px;
}

.nav-indicator {
    flex-shrink: 0;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: white;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.6;
        transform: scale(0.9);
    }
}

@media (max-width: 768px) {
    .admin-sidebar {
        border-radius: 16px;
    }

    .user-profile-card {
        padding: 20px;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
    }

    .user-avatar i {
        font-size: 24px;
    }

    .user-name {
        font-size: 14px;
    }

    .nav-label {
        font-size: 13px;
    }
}
</style>
