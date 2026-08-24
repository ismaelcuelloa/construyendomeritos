import { User } from '@/types/index';

export interface Category {
    id: number;
    slug: string;
    title: string;
    description: string;
    published: boolean;
    active: boolean;
    created_at: string;
    updated_at: string;
    image_id: number;
    courses?: Course[];
    courses_count?: number;
    image?: File;
    enable_custom_filter?: boolean;
    custom_filter_options?: { label: string; values: string[] }[];
    enable_subcategories?: boolean;
    subcategories?: Subcategory[];
}

export interface Subcategory {
    id: number;
    category_id: number;
    parent_id?: number;
    title: string;
    slug: string;
    description: string;
    published: boolean;
    active: boolean;
    created_at: string;
    updated_at: string;
    image_id: number;
    image?: File;
    category?: Category;
    parent?: Subcategory;
    children?: Subcategory[];
    courses?: Course[];
    courses_count?: number;
}

export interface Course {
    id: number;
    code?: string;
    slug: string;
    title: string;
    description: string;
    price: number;
    price_formatted?: string;
    published: boolean;
    active: boolean;
    category_id: number;
    subcategory_id?: number;
    category?: Category;
    subcategory?: Subcategory;
    created_at: string;
    updated_at: string;
    modules?: Module[];
    metadata?: CourseMetadata;
    subscriptions?: Subscription[];
    order_items?: OrderItem[];
}

export interface CourseMetadata {
    id: number;
    course_id: number;
    description: string;
    banner: string;
    color: string;
    custom_filter_value?: string;
    created_at: string;
    updated_at: string;
    course?: Course;
}

export interface Module {
    id: number | string;
    course_id: number;
    title: string;
    description: string;
    pdf_file?: File;
    pdf_url?: string;
    pdf_path?: string;
    file?: File;
    file_url?: string;
    file_path?: string;
    created_at: string;
    updated_at: string;
    course?: Course;
    files?: ModuleFile[];
}

export interface ModuleFile {
    id: number;
    module_id: number;
    file_id: number;
    title: string;
    description: string;
    created_at: string;
    updated_at: string;
    module?: Module;
    file?: File;
}

export interface File {
    id: number;
    name: string;
    path: string;
    type: string;
    url: string;
    created_at: string;
    updated_at: string;
    modules_files?: ModuleFile[];
}

export interface Subscription {
    id: number;
    user_id: number;
    course_id: number;
    enrolled_at: string;
    access_expires_at: string;
    is_expired: boolean;
    created_at: string;
    updated_at: string;
    user?: User;
    course?: Course;
    order?: any;
}

export interface Paginated<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface CartItem {
    id: number;
    title: string;
    price: number;
    quantity: number;
}

export interface Order {
    id: number;
    number: string;
    user_id: number;
    amount: number;
    currency: string;
    status_id: number;
    payu_order_id: string;
    payu_transaction_id: string;
    payment_method: string;
    response_code: string;
    description: string;
    paid_at: string;
    demo_expires_at?: string;
    created_at: string;
    updated_at: string;
    status: string;
    user?: User;
    items?: OrderItem[];
}

export interface OrderItem {
    id: number;
    order_id: number;
    course_id: number;
    description: string;
    subscription_id: number;
    unit_price: number;
    quantity: number;
    total: number;
    created_at: string;
    updated_at: string;
    order?: Order;
    course?: Course;
    subscription?: Subscription;
}
