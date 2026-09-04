export enum ChartMetric {
    Percent = 'percent',
    Count = 'count',
}

export interface ChartItem {
    key: string;
    label: string;
    count: number;
    percent: number;
    rank?: number;
    icon?: string;
    image?: string;
    badge?: string;
    other?: boolean;
    clickable?: boolean;
}

export interface ChartPayload {
    handle: string;
    component: string;
    props: {
        items: ChartItem[];
        drilldown?: {
            items: ChartItem[];
            [prop: string]: unknown;
        };
    };
}

export interface InsightPayload {
    handle: string;
    component: string;
    props: Record<string, unknown>;
}

export interface SummaryField {
    handle: string;
    display: string;
    icon: string;
    fieldtype: string;
    number: number | null;
    responses: number;
    chart: ChartPayload;
    insights: InsightPayload[];
}

export interface ChartConfig {
    field: string;
    chart: string;
}

export interface MetaChart {
    handle: string;
    title: string;
    icon: string | null;
    component: string;
}

export interface MetaField {
    handle: string;
    display: string;
    icon: string;
    default_chart: string;
}

export interface Summary {
    total: number;
    fields: SummaryField[];
    meta: {
        charts?: MetaChart[];
        fields?: MetaField[];
    };
}
