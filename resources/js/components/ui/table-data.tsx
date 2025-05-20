import React from 'react';

type TableDataProps = {
  children: React.ReactNode;
};

export default function TableData({ children }: TableDataProps) {
  return (
    <td className="py-2 md:px-6 px-4 text-sm bg-sidebar text-accent-900">
      {children}
    </td>
  );
}
