import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';

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

export interface Auth {
    user: User;
}

export type FlashProps = {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
};

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

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

declare module '@inertiajs/core' {
    interface PageProps {
        flash: FlashProps;
    }
}
