import type { Category, TransactionDescription } from './category';

export interface RecurringCategoryOption {
    id: number;
    name: string;
    type: string;
    transaction_descriptions: TransactionDescription[];
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
