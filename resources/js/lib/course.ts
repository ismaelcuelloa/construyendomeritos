import type { User } from '@/types';
import { Course } from '@/types/project';

export function isSubscribed(course: Course, user: User | null) {
    if (user === null) return false;
    
    // Check for active subscriptions
    if (course.subscriptions !== undefined && course.subscriptions.length > 0) {
        const hasActiveSubscription = course.subscriptions.some(
            (subscription) => subscription.user_id === user.id && !subscription.is_expired
        );
        if (hasActiveSubscription) return true;
    }
    
    // Check for paid orders (WATI purchases)
    if (course.order_items !== undefined && course.order_items.length > 0) {
        return true; // If order_items exist, it's already filtered by paid status in backend
    }
    
    return false;
}
