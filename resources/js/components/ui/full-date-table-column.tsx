import React from 'react';
import { longDateFormatter } from '@/utils/LongDateFormatter';

type FullDateTableColumnProps = {
  date?: string;
};

export default function FullDateTableColumn({
  date = '2000-01-01 00:00:00',
}: FullDateTableColumnProps) {
  return (
    <td className="py-2 lg:min-w-fit min-w-36 md:px-6 px-4 text-sm capitalize bg-sidebar font-light text-neutral-400">
      {longDateFormatter(date)}
    </td>
  );
}
