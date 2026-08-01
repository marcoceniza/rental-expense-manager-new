import type { PaginatedData, Transaction } from './transaction';

export interface CharityStats {
    expense: number;
    transactions: PaginatedData<Transaction>;
}

export interface CharityTransaction {
    id: number;
    description: string;
    remarks?: string;
    amount: number;
    type: string;
    transaction_date: string;
    category_id: number;
}

export interface OtherStats {
    income: number;
    transactions: PaginatedData<Transaction>;
}
