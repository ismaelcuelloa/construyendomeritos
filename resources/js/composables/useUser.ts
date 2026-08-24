import { Client } from '@/lib/client';
import { ref } from 'vue';

export const user = ref<any>(null);

export async function getUser() {
    if (user.value === null) {
        try {
            user.value = await Client.getUser();
        } catch {
            // Error handled
        }
    }
    return user.value;
}

export function isAdmin(): boolean {
    if (user.value !== null) {
        return user.value.is_admin;
    }
    return false;
}

export function isSuperUser(): boolean {
    if (user.value !== null) {
        return user.value.is_super_user;
    }
    return false;
}
