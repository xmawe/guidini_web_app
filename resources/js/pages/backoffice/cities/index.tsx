import FullDateTableColumn from '@/components/ui/full-date-table-column';
import MetricCard from '@/components/ui/metric-card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import TableData from '@/components/ui/table-data';
import TableHeader from '@/components/ui/table-header';
import TablePagination from '@/components/ui/table-pagination';
import TableRow from '@/components/ui/table-row';
import TextInput from '@/components/ui/text-input';
import TextTableColumn from '@/components/ui/text-table-column';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import type { PageProps } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Edit, MapPinned, Trash } from 'lucide-react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cities',
        href: '/cities',
    },
];

type SearchForm = {
    keyword: string;
};

type Metrics = {
    totalCities: number;
};
type City = {
    id: number;
    name: string;
    userCount: number;
    createdAt: string;
    updatedAt: string;
};
export default function Dashboard() {
    const { metrics, cities } = usePage<{ metrics: Metrics; cities: { data: City[]; meta: any } }>().props;

    const { data, setData, get, errors } = useForm<Required<SearchForm>>({
        keyword: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        get(route('/cities'), {
            // onFinish: () => reset('password'),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <MetricCard icon={MapPinned} label="Total Cities" total={metrics.totalCities.toString()} />
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex min-h-[100vh] flex-col gap-4 overflow-hidden rounded-xl border p-4 md:min-h-min">
                    <div className="full flex flex-col items-center justify-between gap-4 md:flex-row">
                        <form className="flex w-full flex-col gap-6 md:max-w-xs" onSubmit={submit}>
                            <TextInput
                                label=""
                                id="email"
                                type="text"
                                value={data.keyword}
                                onChange={(e) => setData('keyword', e.target.value)}
                                placeholder="Search cities"
                                error={errors.keyword}
                                required
                                tabIndex={1}
                            />
                        </form>
                        <Link
                            className="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-950"
                            href="/cities/create"
                        >
                            Add City
                        </Link>
                    </div>
                    <div className="border-accent-50 w-full overflow-hidden rounded-lg border shadow-sm">
                        <div className="w-full">
                            <table className="h-fit w-full overflow-x-auto">
                                <thead>
                                    <tr className="rounded-t-xl bg-gray-100 text-xs dark:bg-neutral-800 dark:text-gray-600">
                                        <TableHeader label="ID" />
                                        <TableHeader label="Name" />
                                        <TableHeader label="Users" />
                                        <TableHeader label="Created At" />
                                        <TableHeader label="Updated At" />
                                        <TableHeader />
                                    </tr>
                                </thead>
                                <tbody>
                                    {cities.data?.map((city) => (
                                        <TableRow key={city.id}>
                                            <TextTableColumn>{city.id}</TextTableColumn>
                                            <TextTableColumn>{city.name}</TextTableColumn>
                                            <TextTableColumn>{city.userCount}</TextTableColumn>
                                            <FullDateTableColumn date={city.createdAt} />
                                            <FullDateTableColumn date={city.updatedAt} />
                                            <TableData>
                                                <span className="flex items-center justify-end gap-1">
                                                    <button className="hover:bg-primary-25 rounded-lg p-2 duration-300">
                                                        <Trash size={16} />
                                                    </button>
                                                    <button className="rounded-lg p-2 duration-300 hover:bg-red-50">
                                                        <Edit size={16} />
                                                    </button>
                                                </span>
                                            </TableData>
                                        </TableRow>
                                    ))}
                                </tbody>
                            </table>

                            <TablePagination links={cities.meta.links} />
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
