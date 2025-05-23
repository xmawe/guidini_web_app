import React, { useState, useEffect } from 'react';
import { usePage, useForm, Link, router } from '@inertiajs/react';
import type { PageProps } from '@/types';
import AppLayout from '@/layouts/app-layout';
import TableHeader from '@/components/ui/table-header';
import TableRow from '@/components/ui/table-row';
import TextTableColumn from '@/components/ui/text-table-column';
import TablePagination from '@/components/ui/table-pagination';
import MetricCard from '@/components/ui/metric-card';
import Toast from '@/components/ui/toast';
import Modal from '@/components/ui/Modal'; 
import { Head } from '@inertiajs/react';
import { User as UserIcon, Users as UsersIcon } from 'lucide-react';

interface City {
    id: number;
    name: string;
}

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone_number: string | null;
    city: { name: string; id: number } | null;
    role: string;
    last_activity_at: string;
    isOnline: boolean;
}

interface Metrics {
    totalUsers: number;
    onlineUsers: number;
    guides: number;
    admins: number;
}

interface PaginatedUsers {
    data: User[];
    links: any[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface FlashMessage {
    success?: string;
    error?: string;
}

const UsersIndex: React.FC<PageProps<{ users: PaginatedUsers; search: string; filter: string; metrics: Metrics; cities: City[]; flash?: FlashMessage }>> = ({
    users: initialUsers,
    search: initialSearch,
    filter: initialFilter,
    metrics,
    cities,
}) => {
    const [selectedUser, setSelectedUser] = useState<User | null>(null);
    const [isEditing, setIsEditing] = useState(false);
    const [search, setSearch] = useState(initialSearch);
    const [filter, setFilter] = useState(initialFilter);
    const [users, setUsers] = useState<PaginatedUsers>(initialUsers);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [userToDelete, setUserToDelete] = useState<number | null>(null);
    const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

    const { props } = usePage();
    const flash = props.flash as FlashMessage | undefined;

    // Handle flash messages for toast notifications
    useEffect(() => {
        if (flash?.success) {
            setToast({ message: flash.success, type: 'success' });
        } else if (flash?.error) {
            setToast({ message: flash.error, type: 'error' });
        }
    }, [flash]);

    useEffect(() => {
        console.log('Received users data from props:', initialUsers.data);
        setUsers(initialUsers);
    }, [initialUsers]);

    useEffect(() => {
        console.log('Current filter and users:', { filter, users: users.data });
    }, [filter, users.data]);

    const handleFilterChange = (newFilter: string) => {
        setFilter(newFilter);
        console.log('Fetching users with filter:', newFilter);
        const data = { search, filter: newFilter };
        router.get('/users', data, {
            preserveState: false,
            preserveScroll: false,
            replace: true,
            onSuccess: (page) => {
                if (page && page.props && page.props.users) {
                    setUsers(page.props.users as PaginatedUsers);
                }
            },
            onError: (errors) => {
                setToast({ message: 'Failed to fetch users.', type: 'error' });
                console.error('Error fetching users:', errors);
            },
        });
    };

    const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newSearch = e.target.value;
        setSearch(newSearch);
        router.get('/users', { search: newSearch, filter }, {
            preserveState: true,
            preserveScroll: false,
            onSuccess: (page) => {
                if (page && page.props && page.props.users) {
                    setUsers(page.props.users as PaginatedUsers);
                }
            },
            onError: (errors) => {
                setToast({ message: 'Failed to search users.', type: 'error' });
                console.error('Error fetching users:', errors);
            },
        });
    };

    const handleDelete = (id: number) => {
        setUserToDelete(id);
        setShowDeleteModal(true);
    };

    const confirmDelete = () => {
        if (userToDelete) {
            router.delete(`/users/${userToDelete}`, {
                onSuccess: () => {
                    setShowDeleteModal(false);
                    setUserToDelete(null);
                    router.reload({ only: ['users', 'metrics'] });
                },
                onError: (errors) => {
                    setShowDeleteModal(false);
                    setToast({ message: 'Failed to delete user.', type: 'error' });
                    console.error('Error deleting user:', errors);
                },
            });
        }
    };

    const handleEditClick = (user: User) => {
        setSelectedUser(user);
        setIsEditing(true);
        setData({
            first_name: user.first_name,
            last_name: user.last_name,
            email: user.email,
            phone_number: user.phone_number || '',
            city_id: user.city?.id || null,
            role: user.role,
        });
    };

    const { data, setData, put, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        phone_number: '',
        city_id: null as number | null,
        role: '',
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        setData(e.target.name as keyof typeof data, e.target.value);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (selectedUser) {
            put(`/users/${selectedUser.id}`, {
                preserveState: true,
                onSuccess: () => {
                    const updatedUser = { ...selectedUser, ...data, city: cities.find(c => c.id === data.city_id) || null };
                    setSelectedUser(updatedUser);
                    setIsEditing(false);
                    router.reload({ only: ['users', 'metrics'] });
                },
                onError: (errors) => {
                    setToast({ message: 'Failed to update user.', type: 'error' });
                    console.error('Error updating user:', errors);
                },
            });
        }
    };

    const handleCancelEdit = () => {
        setIsEditing(false);
    };

    useEffect(() => {
        if (!selectedUser || !users.data.some(user => user.id === selectedUser.id)) {
            setSelectedUser(null);
            setIsEditing(false);
        }
    }, [users.data, selectedUser]);

    return (
        <AppLayout breadcrumbs={[{ title: 'Users', href: '/users' }]}>
            <Head title="Users" />
            <div className="flex flex-1 flex-col gap-6 p-6 bg-gray-50">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <MetricCard icon={UsersIcon} label="Total Users" total={metrics.totalUsers.toString()} />
                    <MetricCard icon={UserIcon} label="Online Users" total={metrics.onlineUsers.toString()} />
                    <MetricCard icon={UserIcon} label="Guides" total={metrics.guides.toString()} />
                    <MetricCard icon={UserIcon} label="Admins" total={metrics.admins.toString()} />
                </div>
                <div className="bg-white rounded-lg shadow-md p-6">
                    <div className="flex flex-col md:flex-row justify-between items-center mb-6">
                        <div className="flex space-x-4 mb-4 md:mb-0">
                            <button
                                onClick={() => handleFilterChange('all')}
                                className={`px-4 py-2 rounded ${filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}`}
                            >
                                All
                            </button>
                            <button
                                onClick={() => handleFilterChange('online')}
                                className={`px-4 py-2 rounded ${filter === 'online' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}`}
                            >
                                Online
                            </button>
                            <button
                                onClick={() => handleFilterChange('guides')}
                                className={`px-4 py-2 rounded ${filter === 'guides' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}`}
                            >
                                Guides
                            </button>
                            <button
                                onClick={() => handleFilterChange('admins')}
                                className={`px-4 py-2 rounded ${filter === 'admins' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}`}
                            >
                                Admins
                            </button>
                        </div>
                        <input
                            type="text"
                            placeholder="Search users..."
                            className="w-full md:w-64 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value={search}
                            onChange={handleSearchChange}
                        />
                    </div>

                    <div className="flex gap-6">
                        <div className="flex-1">
                            <div className="overflow-hidden border border-gray-200 rounded-lg">
                                <table className="w-full">
                                    <thead className="bg-gray-100">
                                        <tr>
                                            <TableHeader label="ID" />
                                            <TableHeader label="Name" />
                                            <TableHeader label="Email" />
                                            <TableHeader label="Role" />
                                            <TableHeader label="Actions" />
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {users.data.map((user) => (
                                            <tr
                                                key={user.id}
                                                onClick={() => setSelectedUser(user)}
                                                className={`cursor-pointer ${selectedUser?.id === user.id ? 'bg-gray-100' : ''}`}
                                            >
                                                <td>{user.id}</td>
                                                <td>{user.first_name} {user.last_name}</td>
                                                <td>{user.email}</td>
                                                <td>{user.role}</td>
                                                <td>
                                                    <button
                                                        onClick={(e) => { e.stopPropagation(); handleDelete(user.id); }}
                                                        className="text-red-500 hover:text-red-700 mr-2"
                                                    >
                                                        Delete
                                                    </button>
                                                    <button
                                                        onClick={(e) => { e.stopPropagation(); handleEditClick(user); }}
                                                        className="text-blue-500 hover:text-blue-700"
                                                    >
                                                        Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                <TablePagination links={users.links} />
                            </div>
                        </div>
                        {selectedUser && (
                            <div className="w-[350px] min-w-[300px] max-w-[400px] bg-white border border-gray-200 rounded-lg shadow-md p-6">
                                {isEditing ? (
                                    <div>
                                        <h2 className="text-xl font-semibold mb-4">Edit User</h2>
                                        <form onSubmit={handleSubmit} className="space-y-4">
                                            <div className="grid grid-cols-2 gap-4">
                                                <label className="text-sm font-medium text-gray-700">First Name</label>
                                                <div>
                                                    <input
                                                        type="text"
                                                        name="first_name"
                                                        value={data.first_name}
                                                        onChange={handleChange}
                                                        className={`w-full p-2 border rounded-md focus:outline-none focus:ring-2 ${
                                                            errors.first_name ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                                                        }`}
                                                    />
                                                    {errors.first_name && (
                                                        <p className="text-red-500 text-sm mt-1">{errors.first_name}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4">
                                                <label className="text-sm font-medium text-gray-700">Last Name</label>
                                                <div>
                                                    <input
                                                        type="text"
                                                        name="last_name"
                                                        value={data.last_name}
                                                        onChange={handleChange}
                                                        className={`w-full p-2 border rounded-md focus:outline-none focus:ring-2 ${
                                                            errors.last_name ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                                                        }`}
                                                    />
                                                    {errors.last_name && (
                                                        <p className="text-red-500 text-sm mt-1">{errors.last_name}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4">
                                                <label className="text-sm font-medium text-gray-700">City</label>
                                                <select
                                                    name="city_id"
                                                    value={data.city_id || ''}
                                                    onChange={handleChange}
                                                    className="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                >
                                                    <option value="">Select a city</option>
                                                    {cities.map((city) => (
                                                        <option key={city.id} value={city.id}>{city.name}</option>
                                                    ))}
                                                </select>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4">
                                                <label className="text-sm font-medium text-gray-700">Email</label>
                                                <div>
                                                    <input
                                                        type="email"
                                                        name="email"
                                                        value={data.email}
                                                        onChange={handleChange}
                                                        className={`w-full p-2 border rounded-md focus:outline-none focus:ring-2 ${
                                                            errors.email ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                                                        }`}
                                                    />
                                                    {errors.email && (
                                                        <p className="text-red-500 text-sm mt-1">{errors.email}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4">
                                                <label className="text-sm font-medium text-gray-700">Phone Number</label>
                                                <div>
                                                    <input
                                                        type="text"
                                                        name="phone_number"
                                                        value={data.phone_number}
                                                        onChange={handleChange}
                                                        className={`w-full p-2 border rounded-md focus:outline-none focus:ring-2 ${
                                                            errors.phone_number ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
                                                        }`}
                                                    />
                                                    {errors.phone_number && (
                                                        <p className="text-red-500 text-sm mt-1">{errors.phone_number}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4">
                                                <label className="text-sm font-medium text-gray-700">Role</label>
                                                <select
                                                    name="role"
                                                    value={data.role}
                                                    onChange={handleChange}
                                                    className="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                >
                                                    <option value="default">Default</option>
                                                    <option value="admin">Admin</option>
                                                    <option value="guide">Guide</option>
                                                </select>
                                            </div>
                                            <div className="flex justify-end space-x-4 mt-6">
                                                <button
                                                    type="submit"
                                                    disabled={processing}
                                                    className={`px-4 py-2 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                                        processing ? 'bg-blue-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'
                                                    }`}
                                                >
                                                    {processing ? 'Saving...' : 'Save'}
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={handleCancelEdit}
                                                    className="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                ) : (
                                    <div>
                                        <h2 className="text-xl font-semibold mb-4">{selectedUser.first_name} {selectedUser.last_name}</h2>
                                        <div className="space-y-3">
                                            <p className="text-sm"><strong className="text-gray-700">Email:</strong> {selectedUser.email}</p>
                                            <p className="text-sm"><strong className="text-gray-700">Phone:</strong> {selectedUser.phone_number || 'N/A'}</p>
                                            <p className="text-sm"><strong className="text-gray-700">City:</strong> {selectedUser.city?.name || 'N/A'}</p>
                                            <p className="text-sm"><strong className="text-gray-700">Role:</strong> {selectedUser.role}</p>
                                            <p className="text-sm"><strong className="text-gray-700">Last Activity:</strong> {new Date(selectedUser.last_activity_at).toLocaleString()}</p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Toast Notification */}
            {toast && (
                <Toast
                    message={toast.message}
                    type={toast.type}
                    onClose={() => setToast(null)}
                />
            )}

            {/* Delete Confirmation Modal */}
            <Modal
                isOpen={showDeleteModal}
                onClose={() => {
                    setShowDeleteModal(false);
                    setUserToDelete(null);
                }}
                onConfirm={confirmDelete}
                title="Confirm Deletion"
                message="Are you sure you want to delete this user? This action cannot be undone."
            />
        </AppLayout>
    );
};

export default UsersIndex;