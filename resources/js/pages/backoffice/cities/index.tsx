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
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { MapPinned, Trash, Edit, Flame, Building2 } from 'lucide-react';
import DeleteConfirmation from '@/components/ui/delete-confirmation';

type SearchForm = {
    keyword: string;
    sort_by: string;
    sort_order: string;
    per_page: number;
};

type Props = {
    cities: {
        data: Array<{
            id: number;
            name: string;
            userCount: number;
            tourCount: number;
            createdAt: string;
            updatedAt: string;
        }>;
        meta: {
            links: any;
        }
    };
    filters: {
        keyword: string;
        perPage: number;
    };
    sort: {
        by: string;
        order: string;
    };
    metrics: {
        totalCities: number;
        activeCities: number;
        mostActiveCity: {
            name: string;
            total: number;
            userCount: number;
            tourCount: number;
        };
    };
};

export default function Index({ cities, filters, sort, metrics }: Props) {
    // Add loading state for delete operation
    const [isDeleting, setIsDeleting] = useState(false);
    const [deleteCity, setDeleteCity] = useState<number | null>(null);

    // Add console.log to debug the data
    console.log('Cities data:', cities);
    console.log('Filters:', filters);
    console.log('Sort:', sort);
    console.log('Metrics:', metrics);

    const breadcrumbs = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Cities', href: route('cities.index') }
    ];
    const { data, setData, get, errors } = useForm<Required<SearchForm>>({
        keyword: filters.keyword || '',
        sort_by: sort.by || 'name',
        sort_order: sort.order || 'asc',
        per_page: filters.perPage || 10,
    });

    const handleSearch: FormEventHandler = (e) => {
        e.preventDefault();
        get(route('cities.index'));
    };

    const handleShowAll = () => {
        setData('keyword', ' ');
        get(route('cities.index'), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSort = (column: string) => {
        setData({
            ...data,
            sort_by: column,
            sort_order: data.sort_by === column && data.sort_order === 'asc' ? 'desc' : 'asc',
        });
        get(route('cities.index'), {
            preserveState: true,
        });
    };

    const handleDelete = (cityId: number) => {
        setDeleteCity(cityId);
    };

    const confirmDelete = () => {
        if (deleteCity) {
            setIsDeleting(true);
            router.delete(route('cities.destroy', deleteCity), {
                onSuccess: () => {
                    setDeleteCity(null);
                    setIsDeleting(false);
                },
                onError: () => {
                    setIsDeleting(false);
                },
            });
        }
    };

    const getSortIcon = (column: string) => {
        if (data.sort_by !== column) return null;
        return data.sort_order === 'asc' ? '↑' : '↓';
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Cities" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Metrics Cards */}
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <MetricCard
                        icon={MapPinned}
                        label="Total Cities"
                        total={metrics?.totalCities?.toString() || '0'}
                    />
                    <MetricCard
                        icon={Building2}
                        label="Active Cities"
                        total={metrics?.activeCities?.toString() || '0'}
                    />
                    <MetricCard
                        icon={Flame}
                        label="Most Active City"
                        total={metrics?.mostActiveCity?.name || 'N/A'}
                    />
                </div>

                {/* Main Content */}
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex min-h-[100vh] flex-col gap-4 overflow-hidden rounded-xl border p-4 md:min-h-min">
                    {/* Search and Add Button */}
                    <div className="full flex flex-col items-center justify-between gap-4 md:flex-row">
                        <form className="flex w-full flex-col gap-6 md:max-w-xs" onSubmit={handleSearch}>
                            <div className="flex gap-2">
                                <TextInput
                                    id="keyword"
                                    label="Search"
                                    type="text"
                                    value={data.keyword}
                                    onChange={(e) => setData('keyword', e.target.value)}
                                    placeholder="Search cities"
                                    error={errors.keyword}
                                    required={false} // Make it optional
                                />
                                {data.keyword && (
                                    <button
                                        type="button"
                                        onClick={handleShowAll}
                                        className="mt-7 whitespace-nowrap text-sm text-gray-500 hover:text-gray-700"
                                    >
                                        Show All
                                    </button>
                                )}
                            </div>
                        </form>
                        <Link
                            className="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-950"
                            href={route('cities.create')}
                        >
                            Add City
                        </Link>
                    </div>

                    {/* Table */}
                    <div className="border-accent-50 w-full overflow-hidden rounded-lg border shadow-sm">
                        <table className="h-fit w-full overflow-x-auto">
                            <thead>
                                <tr className="rounded-t-xl bg-gray-100 text-xs dark:bg-neutral-800 dark:text-gray-600">
                                    <th onClick={() => handleSort('id')} style={{ cursor: 'pointer' }}>
                                        <TableHeader label={`ID ${getSortIcon('id') || ''}`} />
                                    </th>
                                    <th onClick={() => handleSort('name')} style={{ cursor: 'pointer' }}>
                                        <TableHeader label={`Name ${getSortIcon('name') || ''}`} />
                                    </th>
                                    <th onClick={() => handleSort('userCount')} style={{ cursor: 'pointer' }}>
                                        <TableHeader label={`Users ${getSortIcon('userCount') || ''}`} />
                                    </th>
                                    <th onClick={() => handleSort('tourCount')} style={{ cursor: 'pointer' }}>
                                        <TableHeader label={`Tours ${getSortIcon('tourCount') || ''}`} />
                                    </th>
                                    <TableHeader label="Created At" />
                                    <TableHeader label="Updated At" />
                                    <TableHeader />
                                </tr>
                            </thead>
                            <tbody>
                                {cities?.data?.map((city) => (
                                    <TableRow key={city.id}>
                                        <TextTableColumn>{city.id}</TextTableColumn>
                                        <TextTableColumn>{city.name}</TextTableColumn>
                                        <TextTableColumn>{city.userCount || 0}</TextTableColumn>
                                        <TextTableColumn>{city.tourCount || 0}</TextTableColumn>
                                        <FullDateTableColumn date={city.createdAt} />
                                        <FullDateTableColumn date={city.updatedAt} />
                                        <TableData>
                                            <span className="flex items-center justify-end gap-1">
                                                <Link
                                                    href={route('cities.edit', city.id ) }
                                                    className="rounded-lg p-2 duration-300 hover:bg-red-50"
                                                >
                                                    <Edit size={16} />
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(city.id)}
                                                    className="rounded-lg p-2 duration-300 hover:bg-red-50"
                                                    disabled={isDeleting}
                                                    title="Delete city"
                                                >
                                                    <Trash
                                                        size={16}
                                                        className={isDeleting ? "text-gray-400" : "text-red-500 hover:text-red-600"}
                                                    />
                                                </button>
                                            </span>
                                        </TableData>
                                    </TableRow>
                                ))}
                            </tbody>
                        </table>
                        <TablePagination links={cities?.meta?.links} />
                    </div>
                </div>
            </div>
            <DeleteConfirmation
                isOpen={deleteCity !== null}
                onClose={() => !isDeleting && setDeleteCity(null)}
                onConfirm={confirmDelete}
                title="Delete City"
                message="Are you sure you want to delete this city? This action cannot be undone."
            />
        </AppLayout>
    );
}
