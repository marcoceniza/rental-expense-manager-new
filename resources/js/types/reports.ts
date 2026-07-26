export interface MonthlyData {
    month: string;
    income: number;
    expense: number;
    liability: number;
    net: number;
}

export interface AnnualReport {
    monthly: MonthlyData[];
    totals: {
        income: number;
        expense: number;
        liability: number;
        net: number;
    };
}

export interface CategorySummary {
    categoryReport: Array<{
        name: string;
        type: string;
        total: number;
    }>;
    year?: number;
}
