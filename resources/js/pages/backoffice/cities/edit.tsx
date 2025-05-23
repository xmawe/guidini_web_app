import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import TextInput from '@/components/ui/text-input';
import SuccessNotification from '@/components/ui/success-notification';
import { FormEventHandler } from 'react';
import { router } from '@inertiajs/core';

type Props = {
    city: {
        id: number;
        name: string;
        createdAt: string;
        updatedAt: string;
    };
};

export default function Edit({ city }: Props) {
    const [showSuccess, setShowSuccess] = useState(false);
    const { data, setData, put, processing, errors } = useForm({
        name: city.name,
    });

    const breadcrumbs = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Cities', href: route('cities.index') },
        { title: 'Edit', href: route('cities.edit', { city: city.id }) }
    ];

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('cities.update', city.id), {
            onSuccess: () => {
                setShowSuccess(true);
                // Redirect after 2 seconds
                setTimeout(() => {
                    router.visit(route('cities.index'));
                }, 2000);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit City - ${city.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex flex-col gap-4 overflow-hidden rounded-xl border p-4">
                    <h1 className="text-2xl font-semibold">Edit City</h1>

                    <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                        <TextInput
                            id="name"
                            label="Name"
                            value={data.name}
                            onChange={e => setData('name', e.target.value)}
                            error={errors.name}
                            required
                        />

                        <div className="flex justify-end gap-2">
                            <button
                                type="button"
                                onClick={() => window.history.back()}
                                className="px-4 py-2 text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-950"
                            >
                                Update City
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <SuccessNotification
                show={showSuccess}
                onClose={() => setShowSuccess(false)}
                title="Success!"
                message="City has been updated successfully."
            />
        </AppLayout>
    );
}
