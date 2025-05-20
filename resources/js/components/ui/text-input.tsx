import React from 'react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';

type TextInputProps = {
    id: string;
    label: string;
    type?: string;
    value: string;
    onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
    error?: string;
    placeholder?: string;
    autoFocus?: boolean;
    tabIndex?: number;
    required?: boolean;
    autoComplete?: string;
    disabled?: boolean;
};

export default function TextInput({
    id,
    label,
    type = 'text',
    value,
    onChange,
    error,
    placeholder,
    autoFocus = false,
    tabIndex,
    required = false,
    autoComplete,
    disabled = false,
}: TextInputProps) {
    return (
        <div className="grid">
            {label && <Label className='pb-2' htmlFor={id}>{label}</Label>}
            <Input
                id={id}
                type={type}
                value={value}
                onChange={onChange}
                placeholder={placeholder}
                autoFocus={autoFocus}
                tabIndex={tabIndex}
                required={required}
                autoComplete={autoComplete}
                disabled={disabled}
            />
            <InputError message={error} />
        </div>
    );
}
