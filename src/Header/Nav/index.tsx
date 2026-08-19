'use client'

import React from 'react'

import type { Header as HeaderType } from '@/payload-types'

import { Button } from '@/components/ui/button'
import { CMSLink } from '@/components/Link'
import Link from 'next/link'
import { SearchIcon } from 'lucide-react'

export const HeaderNav: React.FC<{ data: HeaderType }> = ({ data }) => {
  const navItems = data?.navItems || []

  return (
    <nav className="flex items-center gap-6">
      <div className="hidden items-center gap-6 md:flex">
        {navItems.map(({ link }, i) => {
          return (
            <CMSLink
              key={i}
              {...link}
              appearance="link"
              className="text-sm font-medium text-foreground/80 transition-colors hover:text-foreground"
            />
          )
        })}
      </div>
      <Link className="text-foreground/70 transition-colors hover:text-foreground" href="/search">
        <span className="sr-only">Search</span>
        <SearchIcon className="w-[18px]" />
      </Link>
      <Button asChild className="rounded-full" size="sm">
        <Link href="/contact">Subscribe</Link>
      </Button>
    </nav>
  )
}
