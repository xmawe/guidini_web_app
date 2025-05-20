import React from 'react';

type TextTableColumnProps = {
  children: React.ReactNode;
};

export default function TextTableColumn({ children }: TextTableColumnProps) {
  return (
    <td className="py-2 md:px-6 px-4 text-sm font-medium dark:text-neutral-400 text-gray-900 bg-sidebar">
      {children}
    </td>
  );
}
