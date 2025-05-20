import React from 'react';
import { Link } from '@inertiajs/react';

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

type TablePaginationProps = {
  links: PaginationLink[];
};

export default function TablePagination({ links }: TablePaginationProps) {
  const previousPage = links[0];
  const nextPage = links[links.length - 1];
  const pages = links.slice(1, links.length - 1);

  return (
    <div className="flex w-full justify-between gap-4 py-4 px-6 bg-gray-100 text-xs dark:bg-neutral-800">
      {previousPage?.url ? (
        <Link
          href={previousPage.url}
          preserveScroll
          className="flex items-center gap-2 text-sm h-full dark:text-neutral-400 dark:border-neutral-600 select-none bg-sidebar font-semibold text-gray-600 px-3 py-2 border border-gray-300 rounded-lg"
        >
          <span>Previous</span>
        </Link>
      ) : (
        <span className="flex items-center gap-2 text-sm h-full select-none bg-sidebar font-semibold dark:text-neutral-500 text-gray-600 px-3 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg opacity-50">
          <span>Previous</span>
        </span>
      )}

      {/* Page Numbers */}
      <div className="flex items-center gap-1 text-gray-500 dark:text-neutral-500 h-fit font-medium">
        {pages.map((page, index) => (
          page.url ? (
            <Link
              preserveScroll
              key={index}
              href={page.url}
              className={`py-[10px] px-4 rounded-lg text-sm h-fit hover:bg-neutral-50 dark:hover:bg-neutral-700 duration-300 ${
                page.active ? 'bg-neutral-50 dark:bg-neutral-700 text-gray-800 dark:text-neutral-100' : ''
              }`}
              dangerouslySetInnerHTML={{ __html: page.label }}
            />
          ) : (
            <span
              key={index}
              className="py-[10px] px-4 rounded-lg text-sm h-fit text-gray-400 "
              dangerouslySetInnerHTML={{ __html: page.label }}
            />
          )
        ))}
      </div>
      {nextPage?.url ? (
        <Link
          href={nextPage.url}
          preserveScroll
          className="flex items-center gap-2 text-sm h-full dark:text-neutral-400 dark:border-neutral-600 select-none bg-sidebar font-semibold text-gray-600 px-3 py-2 border border-gray-300 rounded-lg"
        >
          <span>Next</span>
        </Link>
      ) : (
        <span className="flex items-center gap-2 text-sm h-full select-none bg-sidebar font-semibold dark:text-neutral-500 text-gray-600 px-3 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg opacity-50">
          <span>Next</span>
        </span>
      )}
    </div>
  );
}
