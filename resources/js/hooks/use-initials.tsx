import { useCallback } from 'react';

export function useInitials() {
    return useCallback((firstName: string, lastName: string): string => {
        if (firstName.length === 0) return '';
        if (lastName.length === 1) return '';

        const firstInitial = firstName[0];
        const lastInitial = lastName[0];

        return `${firstInitial}${lastInitial}`.toUpperCase();
    }, []);
}
