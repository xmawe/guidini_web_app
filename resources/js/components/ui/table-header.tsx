import React from 'react';

type TableHeaderProps = {
  label?: string;
  fontClass?: string;
};

export default function TableHeader({ label = '' }: TableHeaderProps) {
  return (
    <th
      className=
        'font-medium text-start md:px-6 px-4 md:py-3 py-2 uppercase dark:text-neutral-200 text-neutral-900'
    >
      {label}
    </th>
  );
}
