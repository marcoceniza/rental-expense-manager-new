export interface TransactionDescription {
    description: string;
    amount: number;
}

export interface Category {
    id: number;
    name: string;
    type: string;
    is_tuition: boolean;
    is_other: boolean;
    transactions_count?: number;
    transaction_descriptions?: TransactionDescription[];
    created_at?: string;
    updated_at?: string;
}
