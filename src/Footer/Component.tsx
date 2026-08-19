import { getCachedGlobal } from '@/utilities/getGlobals'
import Link from 'next/link'
import React from 'react'

import { ThemeSelector } from '@/providers/Theme/ThemeSelector'
import { CMSLink } from '@/components/Link'
import { Logo } from '@/components/Logo/Logo'

export async function Footer() {
  const footerData = await getCachedGlobal('footer', 1)()

  const navItems = footerData?.navItems || []

  return (
    <footer className="mt-auto border-t border-border bg-secondary/40">
      <div className="container flex flex-col gap-6 py-10 md:flex-row md:items-center md:justify-between">
        <Link className="flex items-center" href="/">
          <Logo className="text-lg" />
        </Link>

        <div className="flex flex-col-reverse items-start gap-4 text-sm text-muted-foreground md:flex-row md:items-center md:gap-6">
          <nav className="flex flex-col gap-2 md:flex-row md:gap-6">
            {navItems.map(({ link }, i) => {
              return (
                <CMSLink
                  className="text-muted-foreground transition-colors hover:text-primary"
                  key={i}
                  {...link}
                />
              )
            })}
          </nav>
          <ThemeSelector />
        </div>
      </div>
    </footer>
  )
}
