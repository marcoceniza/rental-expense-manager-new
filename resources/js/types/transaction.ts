import type { Category } from './category';
import type { User } from './shared';

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
