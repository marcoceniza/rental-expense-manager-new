import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;

    quote: {
        message: string;
        author: string;
    };

    auth: Auth;

    flash: FlashProps;

    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    user_type?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: number;
    name: string;
    type: string;
    is_tuition: boolean;
    is_other: boolean;
    transactions_count?: number;
    created_at?: string;
    updated_at?: string;
}

export interface Transaction {
    id: number;
    user_id: number;
    category_id: number;
    type: string;
    amount: number;
    transaction_date: string;
    description: string;
    remarks?: string;
    recurring_id?: number;
    is_recurring_generated: boolean;
    category?: Category;
    user?: User;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
}

export interface Recurring {
    id: number;
    category_id: number;
    amount: number;
    frequency: string;
    start_date: string;
    end_date?: string;
    description: string;
    last_generated_at?: string;
    category?: Category;
    created_at: string;
    updated_at: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

export type FlashProps = {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
};
declare module '@inertiajs/core' {
    interface PageProps {
        flash: FlashProps;
    }
}

export type BreadcrumbItemType = BreadcrumbItem;
