import React from 'react';
import clsx from 'clsx';

type TableRowProps = {
  active?: boolean;
  children: React.ReactNode;
  onClick?: () => void;
};

export default function TableRow({ active = false, children, onClick }: TableRowProps) {
  return (
    <tr
      className={clsx(
        'border-b cursor-pointer border-b-accent-100 relative w-full',
        {
          'bg-primary-50': active,
        }
      )}
      onClick={onClick}
    >
      {children}
    </tr>
  );
}
