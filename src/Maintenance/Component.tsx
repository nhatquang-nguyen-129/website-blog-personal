import React from 'react'

import type { Header } from '@/payload-types'

import { Logo } from '@/components/Logo/Logo'

type Props = {
  brand?: Header['brand']
  message?: string | null
  title?: string | null
}

export const MaintenancePage: React.FC<Props> = ({ title, message, brand }) => {
  return (
    <div className="flex min-h-[100vh] flex-col items-center justify-center gap-6 bg-secondary/40 px-6 text-center">
      <div className="flex size-16 items-center justify-center rounded-full border-2 border-primary text-primary">
        <svg
          aria-hidden="true"
          className="size-8"
          fill="none"
          stroke="currentColor"
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={1.5}
          viewBox="0 0 24 24"
        >
          <circle cx="12" cy="12" r="9" />
          <path d="M12 7v5l3 3" />
        </svg>
      </div>

      <div className="flex flex-col items-center gap-3">
        <h1 className="font-serif text-2xl font-semibold text-foreground">
          {title || 'Site under maintenance'}
        </h1>
        <div className="h-px w-10 bg-primary" />
        <p className="max-w-sm text-muted-foreground">
          {message || "We're upgrading things to bring you a better experience. Back soon."}
        </p>
      </div>

      <Logo brand={brand} className="mt-4 text-base opacity-60" />
    </div>
  )
}
