import React from 'react';
import clsx from 'clsx';
import type { LucideIcon } from 'lucide-react';

type SubMetric = {
    label: string;
    total: string;
    color?: string | null;
};

type MetricCardProps = {
    icon: LucideIcon;
    label?: string;
    total?: string;
    subMetrics?: SubMetric[];
    alignRight?: boolean;
};

export default function MetricCard({
    icon : Icon,
    label = 'Label',
    total = '0',
}: MetricCardProps) {
    return (
        <div
            className={clsx(
                'py-5 px-6 bg-sidebar border-accent-50 shadow-sm w-full min-w-72 border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border',
            )}
        >
            <h1 className="md:text-4xl text-3xl dark:text-white text-gray-900 pb-2 font-bold">
                {total}
            </h1>
            <h2 className="dark:text-gray-400 text-gray-600 text-sm font-medium">{label}</h2>

            <div className='absolute right-0 -bottom-3 '>
                {Icon && <Icon color='gray'  size="72" />}
            </div>
        </div>
    );
}
